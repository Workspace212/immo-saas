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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Invoice::query()
            ->with(['client', 'owner', 'contract', 'creator'])
            ->latest();

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
        $user = $request->user();
        $invoice = $this->invoiceService->create(
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return (new InvoiceResource($this->freshInvoice($invoice)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($this->freshInvoice($invoice));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $user = $request->user();
        $invoice = $this->invoiceService->update(
            $invoice,
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return new InvoiceResource($this->freshInvoice($invoice));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->invoiceService->delete($invoice);

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }

    public function markSent(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($this->freshInvoice($this->invoiceService->markSent($invoice)));
    }

    public function markPaid(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($this->freshInvoice($this->invoiceService->markPaid($invoice)));
    }

    public function markPartiallyPaid(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($this->freshInvoice($this->invoiceService->markPartiallyPaid($invoice)));
    }

    public function markOverdue(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($this->freshInvoice($this->invoiceService->markOverdue($invoice)));
    }

    public function cancel(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($this->freshInvoice($this->invoiceService->cancel($invoice)));
    }

    public function duplicate(Invoice $invoice): JsonResponse
    {
        return (new InvoiceResource($this->freshInvoice($this->invoiceService->duplicate($invoice))))
            ->response()
            ->setStatusCode(201);
    }

    public function generatePdf(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($this->freshInvoice($this->invoiceService->generatePdf($invoice)));
    }

    private function freshInvoice(Invoice $invoice): Invoice
    {
        return $invoice->refresh()->load(['client', 'owner', 'contract', 'creator']);
    }
}
