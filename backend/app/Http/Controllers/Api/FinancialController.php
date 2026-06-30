<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinancialTransactionRequest;
use App\Http\Requests\UpdateFinancialTransactionRequest;
use App\Http\Resources\FinancialTransactionResource;
use App\Models\FinancialTransaction;
use App\Services\FinancialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    public function __construct(private readonly FinancialService $financialService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = FinancialTransaction::query()
            ->with(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])
            ->latest('transaction_date')
            ->latest('id');

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
        $data = $this->normalizeTransactionData($request->validated());
        $data['created_by'] ??= $request->user()?->getKey();

        $transaction = $this->financialService->createFinancialTransaction($data);

        return (new FinancialTransactionResource($transaction->load(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(FinancialTransaction $financialTransaction): FinancialTransactionResource
    {
        return new FinancialTransactionResource(
            $financialTransaction->load(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])
        );
    }

    public function update(UpdateFinancialTransactionRequest $request, FinancialTransaction $financialTransaction): FinancialTransactionResource
    {
        $transaction = DB::transaction(function () use ($request, $financialTransaction): FinancialTransaction {
            // TODO: Move update workflow into FinancialService when posting and audit rules are finalized.
            $financialTransaction->fill($this->normalizeTransactionData($request->validated()));
            $financialTransaction->save();

            return $financialTransaction->refresh();
        });

        return new FinancialTransactionResource(
            $transaction->load(['sourceAccount', 'destinationAccount', 'financialCategory', 'revenueCenter.property', 'creator', 'validator'])
        );
    }

    public function destroy(FinancialTransaction $financialTransaction): JsonResponse
    {
        DB::transaction(function () use ($financialTransaction): void {
            // TODO: Prevent deletion of validated, reconciled, or closed-period transactions.
            $financialTransaction->delete();
        });

        return response()->json(null, 204);
    }

    public function validateTransaction(FinancialTransaction $financialTransaction): FinancialTransactionResource
    {
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
        $validated = $request->validate([
            'agency_id' => ['required', 'integer', 'exists:agencies,id'],
            'date' => ['required', 'date'],
        ]);

        $closing = $this->financialService->closeFinancialPeriod(
            (int) $validated['agency_id'],
            Carbon::parse($validated['date']),
            $request->user()
        );

        return response()->json([
            'data' => $closing,
        ], 201);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agency_id' => ['required', 'integer', 'exists:agencies,id'],
        ]);

        return response()->json([
            'data' => [
                'balance' => $this->financialService->calculateAgencyBalance((int) $validated['agency_id']),
                'kpis' => $this->financialService->generateFinancialKPIs((int) $validated['agency_id']),
            ],
        ]);
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
