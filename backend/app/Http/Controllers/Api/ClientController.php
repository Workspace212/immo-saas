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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clientService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::query()
            ->with('agency')
            ->latest();

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
        $user = $request->user();
        $client = $this->clientService->create(
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return (new ClientResource($this->freshClient($client)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Client $client): ClientResource
    {
        return new ClientResource($this->freshClient($client));
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $user = $request->user();
        $data = $request->validated();
        $client = $this->clientService->update($client, $data, $user instanceof User ? $user : null);

        if (isset($data['assigned_agent'])) {
            $agent = User::query()->findOrFail((int) $data['assigned_agent']);
            $client = $this->clientService->assignAgent($client, $agent);
        }

        return new ClientResource($this->freshClient($client));
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->clientService->delete($client);

        return response()->json([
            'message' => 'Client deleted successfully.',
        ]);
    }

    public function archive(Client $client): ClientResource
    {
        return new ClientResource($this->freshClient(
            $this->clientService->archive($client)
        ));
    }

    public function restore(Client $client): ClientResource
    {
        return new ClientResource($this->freshClient(
            $this->clientService->restore($client)
        ));
    }

    private function freshClient(Client $client): Client
    {
        return $client->refresh()->load('agency');
    }
}
