<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Models\ContractParty;
use App\Models\User;
use App\Services\ContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    public function __construct(private readonly ContractService $contractService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Contract::query()
            ->with(['property', 'creator', 'assignedAgent'])
            ->latest();

        foreach (['status', 'contract_type', 'property_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('owner_id')) {
            $query->whereIn('id', ContractParty::query()
                ->select('contract_id')
                ->where('owner_id', $request->integer('owner_id')));
        }

        if ($request->filled('client_id')) {
            $query->whereIn('id', ContractParty::query()
                ->select('contract_id')
                ->where('client_id', $request->integer('client_id')));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->date('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->date('end_date'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('contract_number', 'like', $keyword)
                    ->orWhere('contract_type', 'like', $keyword)
                    ->orWhere('status', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return ContractResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreContractRequest $request): JsonResponse
    {
        $user = $request->user();
        $contract = $this->contractService->createContract(
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return (new ContractResource($this->freshContract($contract)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Contract $contract): ContractResource
    {
        return new ContractResource($this->freshContract($contract));
    }

    public function update(UpdateContractRequest $request, Contract $contract): ContractResource
    {
        $user = $request->user();
        $contract = $this->contractService->updateContract(
            $contract,
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return new ContractResource($this->freshContract($contract));
    }

    public function destroy(Contract $contract): JsonResponse
    {
        $contract->delete();

        return response()->json([
            'message' => 'Contract deleted successfully.',
        ]);
    }

    public function renew(Contract $contract): JsonResponse
    {
        $renewedContract = $this->contractService->renewContract($contract, []);

        return (new ContractResource($this->freshContract($renewedContract)))
            ->response()
            ->setStatusCode(201);
    }

    public function archive(Contract $contract): ContractResource
    {
        $contract = $this->contractService->expireContract($contract);

        return new ContractResource($this->freshContract($contract));
    }

    private function freshContract(Contract $contract): Contract
    {
        return $contract->refresh()->load(['property', 'creator', 'assignedAgent']);
    }
}
