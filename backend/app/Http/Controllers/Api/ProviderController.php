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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProviderController extends Controller
{
    public function __construct(private readonly ProviderService $providerService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Provider::query()
            ->with('agency')
            ->latest();

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
        $user = $request->user();
        $provider = $this->providerService->create(
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return (new ProviderResource($this->freshProvider($provider)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Provider $provider): ProviderResource
    {
        return new ProviderResource($this->freshProvider($provider));
    }

    public function update(UpdateProviderRequest $request, Provider $provider): ProviderResource
    {
        $user = $request->user();
        $provider = $this->providerService->update(
            $provider,
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return new ProviderResource($this->freshProvider($provider));
    }

    public function destroy(Provider $provider): JsonResponse
    {
        $provider->delete();

        return response()->json([
            'message' => 'Provider deleted successfully.',
        ]);
    }

    public function activate(Provider $provider): ProviderResource
    {
        return new ProviderResource($this->freshProvider(
            $this->providerService->activate($provider)
        ));
    }

    public function deactivate(Provider $provider): ProviderResource
    {
        return new ProviderResource($this->freshProvider(
            $this->providerService->deactivate($provider)
        ));
    }

    private function freshProvider(Provider $provider): Provider
    {
        return $provider->refresh()->load('agency');
    }
}
