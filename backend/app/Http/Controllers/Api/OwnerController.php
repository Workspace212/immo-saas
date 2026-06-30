<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use App\Http\Resources\OwnerResource;
use App\Models\Contract;
use App\Models\ContractParty;
use App\Models\Owner;
use App\Services\OwnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OwnerController extends Controller
{
    public function __construct(private readonly OwnerService $ownerService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Owner::query()
            ->with('agency')
            ->latest();

        if ($request->filled('owner_type')) {
            $query->where('type', $request->input('owner_type'));
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
                ->whereNotNull('owner_id')
                ->select('owner_id'));
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
                    ->orWhere('bank_name', 'like', $keyword)
                    ->orWhere('rib_iban', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return OwnerResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreOwnerRequest $request): JsonResponse
    {
        $owner = $this->ownerService->create($request->validated(), $request->user());

        return (new OwnerResource($this->freshOwner($owner)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Owner $owner): OwnerResource
    {
        return new OwnerResource($this->freshOwner($owner));
    }

    public function update(UpdateOwnerRequest $request, Owner $owner): OwnerResource
    {
        $owner = $this->ownerService->update($owner, $request->validated(), $request->user());

        return new OwnerResource($this->freshOwner($owner));
    }

    public function destroy(Owner $owner): JsonResponse
    {
        $this->ownerService->delete($owner);

        return response()->json([
            'message' => 'Owner deleted successfully.',
        ]);
    }

    public function archive(Owner $owner): OwnerResource
    {
        return new OwnerResource($this->freshOwner(
            $this->ownerService->archive($owner)
        ));
    }

    public function restore(Owner $owner): OwnerResource
    {
        return new OwnerResource($this->freshOwner(
            $this->ownerService->restore($owner)
        ));
    }

    private function freshOwner(Owner $owner): Owner
    {
        return $owner->refresh()->load('agency');
    }
}
