<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use App\Models\User;
use App\Services\ProviderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProviderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ProviderService $providerService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Provider::class);

        $query = Provider::query()
            ->with('agency')
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        foreach (['provider_type', 'city'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('rating_min')) {
            $query->where('rating', '>=', $request->input('rating_min'));
        }

        if ($request->filled('rating_max')) {
            $query->where('rating', '<=', $request->input('rating_max'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('company_name', 'like', $keyword)
                    ->orWhere('contact_name', 'like', $keyword)
                    ->orWhere('provider_type', 'like', $keyword)
                    ->orWhere('phone', 'like', $keyword)
                    ->orWhere('whatsapp', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword)
                    ->orWhere('address', 'like', $keyword)
                    ->orWhere('city', 'like', $keyword)
                    ->orWhere('ice', 'like', $keyword)
                    ->orWhere('rc', 'like', $keyword)
                    ->orWhere('if_number', 'like', $keyword)
                    ->orWhere('patente', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return ProviderResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreProviderRequest $request): JsonResponse
    {
        $this->authorize('create', Provider::class);

        $user = $request->user();
        $provider = $this->providerService->create(
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return (new ProviderResource($this->freshProvider($provider)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Provider $provider): ProviderResource
    {
        $this->authorize('view', $provider);

        return new ProviderResource($this->freshProvider($provider));
    }

    public function update(UpdateProviderRequest $request, Provider $provider): ProviderResource
    {
        $this->authorize('update', $provider);

        $user = $request->user();
        $provider = $this->providerService->update(
            $provider,
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return new ProviderResource($this->freshProvider($provider));
    }

    public function destroy(Provider $provider): JsonResponse
    {
        $this->authorize('delete', $provider);

        $provider->delete();

        return response()->json([
            'message' => 'Provider deleted successfully.',
        ]);
    }

    public function activate(Provider $provider): ProviderResource
    {
        $this->authorize('update', $provider);

        return new ProviderResource($this->freshProvider(
            $this->providerService->activate($provider)
        ));
    }

    public function deactivate(Provider $provider): ProviderResource
    {
        $this->authorize('update', $provider);

        return new ProviderResource($this->freshProvider(
            $this->providerService->deactivate($provider)
        ));
    }

    private function freshProvider(Provider $provider): Provider
    {
        return $provider->refresh()->load('agency');
    }

    private function applyIndexAuthorization($query, Request $request): void
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401, 'Unauthenticated.');

        if ($user->hasAnyRole(['manager', 'assistant'])) {
            return;
        }

        // TODO: provider policy is still closed and providers have no user/provider ownership link for safe list filtering.
        $query->whereRaw('1 = 0');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function tenantData(array $data, mixed $user): array
    {
        if ($user instanceof User) {
            $data['agency_id'] = $user->agency_id;
        }

        return $data;
    }
}
