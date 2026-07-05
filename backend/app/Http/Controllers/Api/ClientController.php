<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractParty;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ClientService $clientService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::query()
            ->with('agency')
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        if ($request->filled('client_type')) {
            $query->where('type', $request->input('client_type'));
        }

        foreach (['city', 'country'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->has('is_active')) {
            $query->where('status', $request->boolean('is_active') ? 'active' : 'archived');
        }

        if ($request->filled('assigned_agent')) {
            $contractIds = Contract::query()
                ->where('assigned_agent_id', $request->integer('assigned_agent'))
                ->select('id');

            $query->whereIn('id', ContractParty::query()
                ->whereIn('contract_id', $contractIds)
                ->whereNotNull('client_id')
                ->select('client_id'));
        }

        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->date('created_at'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('full_name', 'like', $keyword)
                    ->orWhere('company_name', 'like', $keyword)
                    ->orWhere('cin_passport', 'like', $keyword)
                    ->orWhere('phone', 'like', $keyword)
                    ->orWhere('whatsapp', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword)
                    ->orWhere('address', 'like', $keyword)
                    ->orWhere('city', 'like', $keyword)
                    ->orWhere('country', 'like', $keyword)
                    ->orWhere('ice', 'like', $keyword)
                    ->orWhere('rc', 'like', $keyword)
                    ->orWhere('if_number', 'like', $keyword)
                    ->orWhere('patente', 'like', $keyword)
                    ->orWhere('representative_name', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return ClientResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $user = $request->user();
        $client = $this->clientService->create(
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return (new ClientResource($this->freshClient($client)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Client $client): ClientResource
    {
        $this->authorize('view', $client);

        return new ClientResource($this->freshClient($client));
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $this->authorize('update', $client);

        $user = $request->user();
        $data = $this->tenantData($request->validated(), $user);
        $client = $this->clientService->update($client, $data, $user instanceof User ? $user : null);

        if (isset($data['assigned_agent'])) {
            $agent = User::query()->findOrFail((int) $data['assigned_agent']);
            $client = $this->clientService->assignAgent($client, $agent);
        }

        return new ClientResource($this->freshClient($client));
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $this->clientService->delete($client);

        return response()->json([
            'message' => 'Client deleted successfully.',
        ]);
    }

    public function archive(Client $client): ClientResource
    {
        $this->authorize('archive', $client);

        return new ClientResource($this->freshClient(
            $this->clientService->archive($client)
        ));
    }

    public function restore(Client $client): ClientResource
    {
        $this->authorize('restore', $client);

        return new ClientResource($this->freshClient(
            $this->clientService->restore($client)
        ));
    }

    private function freshClient(Client $client): Client
    {
        return $client->refresh()->load('agency');
    }

    private function applyIndexAuthorization($query, Request $request): void
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401, 'Unauthenticated.');

        if ($user->hasAnyRole(['manager', 'assistant'])) {
            return;
        }

        if ($user->hasRole('agent')) {
            $userId = (int) $user->getKey();

            $query->where(function ($builder) use ($userId): void {
                $builder->whereExists(function ($subquery) use ($userId): void {
                    $subquery->selectRaw('1')
                        ->from('contract_parties')
                        ->join('contracts', 'contracts.id', '=', 'contract_parties.contract_id')
                        ->whereColumn('contract_parties.client_id', 'clients.id')
                        ->where('contracts.assigned_agent_id', $userId);
                })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('rental_parties')
                            ->join('rental_units', 'rental_units.id', '=', 'rental_parties.rental_unit_id')
                            ->whereColumn('rental_parties.client_id', 'clients.id')
                            ->where('rental_units.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('complaints')
                            ->whereColumn('complaints.client_id', 'clients.id')
                            ->where('complaints.assigned_to', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('appointments')
                            ->whereColumn('appointments.client_id', 'clients.id')
                            ->where('appointments.created_by', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('collaborations')
                            ->whereColumn('collaborations.client_id', 'clients.id')
                            ->where(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->where('collaborations.requesting_agent_id', $userId)
                                    ->orWhere('collaborations.owner_agent_id', $userId)
                                    ->orWhere('collaborations.created_by', $userId);
                            });
                    });
            });

            return;
        }

        // TODO: employees and client portal users need explicit user-client links before listing clients.
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
