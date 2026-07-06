<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceService;
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly InvoiceService $invoiceService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()
            ->with(['client', 'owner', 'contract', 'creator'])
            ->latest();

        $this->applyIndexAuthorization($query, $request);

        foreach (['status', 'client_id', 'owner_id', 'contract_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('invoice_type')) {
            $query->where('notes', 'like', '%' . $request->string('invoice_type')->toString() . '%');
        }

        if ($request->filled('property_id')) {
            $query->whereIn('contract_id', Contract::query()
                ->where('property_id', $request->integer('property_id'))
                ->select('id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('status', $request->input('payment_status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issued_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issued_at', '<=', $request->date('date_to'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('invoice_number', 'like', $keyword)
                    ->orWhere('status', 'like', $keyword)
                    ->orWhere('currency', 'like', $keyword)
                    ->orWhere('notes', 'like', $keyword);
            });
        }

        return InvoiceResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $user = $request->user();
        $invoice = $this->invoiceService->create(
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return (new InvoiceResource($this->freshInvoice($invoice)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($this->freshInvoice($invoice));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $this->authorize('update', $invoice);

        $user = $request->user();
        $invoice = $this->invoiceService->update(
            $invoice,
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return new InvoiceResource($this->freshInvoice($invoice));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $this->invoiceService->delete($invoice);

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }

    public function markSent(Invoice $invoice): InvoiceResource
    {
        $this->authorize('markSent', $invoice);

        return new InvoiceResource($this->freshInvoice($this->invoiceService->markSent($invoice)));
    }

    public function markPaid(Invoice $invoice): InvoiceResource
    {
        $this->authorize('markPaid', $invoice);

        return new InvoiceResource($this->freshInvoice($this->invoiceService->markPaid($invoice)));
    }

    public function markPartiallyPaid(Invoice $invoice): InvoiceResource
    {
        $this->authorize('markPartiallyPaid', $invoice);

        return new InvoiceResource($this->freshInvoice($this->invoiceService->markPartiallyPaid($invoice)));
    }

    public function markOverdue(Invoice $invoice): InvoiceResource
    {
        $this->authorize('markOverdue', $invoice);

        return new InvoiceResource($this->freshInvoice($this->invoiceService->markOverdue($invoice)));
    }

    public function cancel(Invoice $invoice): InvoiceResource
    {
        $this->authorize('cancel', $invoice);

        return new InvoiceResource($this->freshInvoice($this->invoiceService->cancel($invoice)));
    }

    public function duplicate(Invoice $invoice): JsonResponse
    {
        $this->authorize('duplicate', $invoice);

        return (new InvoiceResource($this->freshInvoice($this->invoiceService->duplicate($invoice))))
            ->response()
            ->setStatusCode(201);
    }

    public function generatePdf(Invoice $invoice): InvoiceResource
    {
        $this->authorize('generatePdf', $invoice);

        return new InvoiceResource($this->freshInvoice($this->invoiceService->generatePdf($invoice)));
    }

    private function freshInvoice(Invoice $invoice): Invoice
    {
        return $invoice->refresh()->load(['client', 'owner', 'contract', 'creator']);
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
                $builder->where('created_by', $userId)
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('contracts')
                            ->whereColumn('contracts.id', 'invoices.contract_id')
                            ->where(function ($contractQuery) use ($userId): void {
                                $contractQuery->where('contracts.assigned_agent_id', $userId)
                                    ->orWhere('contracts.created_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('contracts')
                            ->join('properties', 'properties.id', '=', 'contracts.property_id')
                            ->whereColumn('contracts.id', 'invoices.contract_id')
                            ->where(function ($propertyQuery) use ($userId): void {
                                $propertyQuery->where('properties.created_by', $userId)
                                    ->orWhere('properties.updated_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('rental_units')
                            ->whereColumn('rental_units.contract_id', 'invoices.contract_id')
                            ->where('rental_units.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('contracts')
                            ->join('complaints', 'complaints.property_id', '=', 'contracts.property_id')
                            ->whereColumn('contracts.id', 'invoices.contract_id')
                            ->where('complaints.assigned_to', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('contracts')
                            ->join('collaborations', 'collaborations.property_id', '=', 'contracts.property_id')
                            ->whereColumn('contracts.id', 'invoices.contract_id')
                            ->where(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->where('collaborations.requesting_agent_id', $userId)
                                    ->orWhere('collaborations.owner_agent_id', $userId)
                                    ->orWhere('collaborations.created_by', $userId);
                            });
                    });
            });

            return;
        }

        // TODO: employees and portal users need explicit invoice relationship filters before listing invoices.
        $query->whereRaw('1 = 0');
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
}
