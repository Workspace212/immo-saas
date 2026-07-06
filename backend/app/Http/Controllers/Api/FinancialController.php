<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinancialTransactionRequest;
use App\Http\Requests\UpdateFinancialTransactionRequest;
use App\Http\Resources\FinancialTransactionResource;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Services\FinancialService;
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly FinancialService $financialService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $query = FinancialTransaction::query()
            ->with(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])
            ->latest('transaction_date')
            ->latest('id');

        $this->applyIndexAuthorization($query, $request);

        if ($request->filled('account')) {
            $accountId = $request->integer('account');
            $query->where(function ($builder) use ($accountId): void {
                $builder->where('source_account_id', $accountId)
                    ->orWhere('destination_account_id', $accountId);
            });
        }

        if ($request->filled('category')) {
            $query->where('financial_category_id', $request->integer('category'));
        }

        foreach (['status', 'transaction_type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date('date_to'));
        }

        if ($request->filled('property_id')) {
            $query->whereHas('revenueCenter', function ($builder) use ($request): void {
                $builder->where('property_id', $request->integer('property_id'));
            });
        }

        // TODO: Apply contract, owner, client, and provider filters when financial transactions receive these relationships.

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('transaction_number', 'like', $keyword)
                    ->orWhere('reference', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return FinancialTransactionResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreFinancialTransactionRequest $request): JsonResponse
    {
        $this->authorize('create', FinancialTransaction::class);

        $data = $this->tenantData($this->normalizeTransactionData($request->validated()), $request->user());
        $data['created_by'] ??= $request->user()?->getKey();

        $transaction = $this->financialService->createFinancialTransaction($data);

        return (new FinancialTransactionResource($transaction->load(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(FinancialTransaction $financialTransaction): FinancialTransactionResource
    {
        $this->authorize('view', $financialTransaction);

        return new FinancialTransactionResource(
            $financialTransaction->load(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])
        );
    }

    public function update(UpdateFinancialTransactionRequest $request, FinancialTransaction $financialTransaction): FinancialTransactionResource
    {
        $this->authorize('update', $financialTransaction);

        $transaction = DB::transaction(function () use ($request, $financialTransaction): FinancialTransaction {
            // TODO: Move update workflow into FinancialService when posting and audit rules are finalized.
            $financialTransaction->fill($this->tenantData($this->normalizeTransactionData($request->validated()), $request->user()));
            $financialTransaction->save();

            return $financialTransaction->refresh();
        });

        return new FinancialTransactionResource(
            $transaction->load(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])
        );
    }

    public function destroy(FinancialTransaction $financialTransaction): JsonResponse
    {
        $this->authorize('delete', $financialTransaction);

        DB::transaction(function () use ($financialTransaction): void {
            // TODO: Prevent deletion of validated, reconciled, or closed-period transactions.
            $financialTransaction->delete();
        });

        return response()->json(null, 204);
    }

    public function validateTransaction(FinancialTransaction $financialTransaction): FinancialTransactionResource
    {
        $this->authorize('validateTransaction', $financialTransaction);

        $transaction = DB::transaction(function () use ($financialTransaction): FinancialTransaction {
            // TODO: Post transaction to account balances and enforce validation authorization.
            $financialTransaction->fill([
                'status' => 'validated',
                'validated_by' => request()->user()?->getKey(),
                'validated_at' => Carbon::now(),
            ]);
            $financialTransaction->save();

            return $financialTransaction->refresh();
        });

        return new FinancialTransactionResource(
            $transaction->load(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])
        );
    }

    public function cancelTransaction(FinancialTransaction $financialTransaction): FinancialTransactionResource
    {
        $this->authorize('cancelTransaction', $financialTransaction);

        $transaction = DB::transaction(function () use ($financialTransaction): FinancialTransaction {
            // TODO: Reverse posted balances and require cancellation reason when accounting rules are finalized.
            $financialTransaction->fill(['status' => 'cancelled']);
            $financialTransaction->save();

            return $financialTransaction->refresh();
        });

        return new FinancialTransactionResource(
            $transaction->load(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])
        );
    }

    public function closePeriod(Request $request): JsonResponse
    {
        $this->authorize('closePeriod', FinancialTransaction::class);

        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);
        $agencyId = $this->tenantAgencyId($request);

        $closing = $this->financialService->closeFinancialPeriod(
            $agencyId,
            Carbon::parse($validated['date']),
            $request->user()
        );

        return response()->json([
            'data' => $closing,
        ], 201);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('dashboard', FinancialTransaction::class);

        $agencyId = $this->tenantAgencyId($request);

        return response()->json([
            'data' => [
                'balance' => $this->financialService->calculateAgencyBalance($agencyId),
                'kpis' => $this->financialService->generateFinancialKPIs($agencyId),
            ],
        ]);
    }

    private function applyIndexAuthorization($query, Request $request): void
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401, 'Unauthenticated.');

        if ($user->hasAnyRole(['manager', 'assistant', 'employee'])) {
            return;
        }

        // TODO: financial transactions do not expose safe agent/external relationship filters yet.
        $query->whereRaw('1 = 0');
    }

    private function tenantAgencyId(Request $request): int
    {
        $agencyId = app(TenantContext::class)->agencyId();
        $user = $request->user();

        if ($agencyId === null && $user instanceof User) {
            $agencyId = $user->agency_id === null ? null : (int) $user->agency_id;
        }

        abort_if($agencyId === null, 403, 'Tenant agency is required.');

        return $agencyId;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function tenantData(array $data, mixed $user): array
    {
        $agencyId = app(TenantContext::class)->agencyId();

        if ($agencyId === null && $user instanceof User) {
            $agencyId = $user->agency_id === null ? null : (int) $user->agency_id;
        }

        if ($agencyId !== null) {
            $data['agency_id'] = $agencyId;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeTransactionData(array $data): array
    {
        if (isset($data['transaction']) && is_array($data['transaction'])) {
            $data['transaction_type'] ??= $data['transaction']['type'] ?? null;
            $data['transaction_date'] ??= $data['transaction']['date'] ?? null;
            $data['amount'] ??= $data['transaction']['amount'] ?? null;
            $data['reference'] ??= $data['transaction']['reference'] ?? null;
        }

        if (isset($data['account']) && is_array($data['account'])) {
            $data['source_account_id'] ??= $data['account']['source_id'] ?? null;
            $data['destination_account_id'] ??= $data['account']['destination_id'] ?? null;
        }

        if (isset($data['category']) && is_array($data['category'])) {
            $data['financial_category_id'] ??= $data['category']['id'] ?? null;
        }

        if (($data['attachment_path'] ?? null) === null && isset($data['attachments'][0]['path'])) {
            $data['attachment_path'] = $data['attachments'][0]['path'];
        }

        unset($data['transaction'], $data['attachments'], $data['tax'], $data['category'], $data['account']);

        return $data;
    }
}
