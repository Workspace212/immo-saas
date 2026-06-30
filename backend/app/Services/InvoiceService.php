<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    private const RELATION_KEYS = ['lines', 'payments', 'taxes', 'discounts'];

    public function create(array $data, ?User $user = null): Invoice
    {
        return DB::transaction(function () use ($data, $user): Invoice {
            $lines = $data['lines'] ?? [];
            $payments = $data['payments'] ?? [];
            $invoiceData = $this->invoiceData($data);
            $invoiceData['invoice_number'] ??= $this->generateInvoiceNumber();
            $invoiceData['status'] ??= 'draft';
            $invoiceData['currency'] ??= 'MAD';

            if ($user !== null) {
                $invoiceData['created_by'] = $user->getKey();
            }

            // TODO: Validate invoice recipient, taxable profile, and accounting period rules.
            $invoice = Invoice::query()->create($invoiceData);

            if (is_array($lines)) {
                $this->syncLines($invoice, $lines);
            }

            if (is_array($payments)) {
                foreach ($payments as $paymentData) {
                    if (is_array($paymentData)) {
                        $this->createPayment($invoice, $paymentData, $user);
                    }
                }
            }

            return $this->recalculate($invoice);
        });
    }

    public function update(Invoice $invoice, array $data, ?User $user = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $user): Invoice {
            $invoiceData = $this->invoiceData($data);

            // TODO: Restrict updates once an invoice is sent, paid, or fiscally locked.
            unset($user);

            if ($invoiceData !== []) {
                $invoice->fill($invoiceData);
                $invoice->save();
            }

            if (array_key_exists('lines', $data) && is_array($data['lines'])) {
                $this->syncLines($invoice, $data['lines']);
            }

            if (array_key_exists('payments', $data) && is_array($data['payments'])) {
                foreach ($data['payments'] as $paymentData) {
                    if (is_array($paymentData)) {
                        $this->createPayment($invoice, $paymentData);
                    }
                }
            }

            return $this->recalculate($invoice);
        });
    }

    public function duplicate(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice): Invoice {
            $newInvoice = $invoice->replicate([
                'invoice_number',
                'status',
                'paid_amount',
                'remaining_amount',
                'pdf_path',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);

            $newInvoice->invoice_number = $this->generateInvoiceNumber();
            $newInvoice->status = 'draft';
            $newInvoice->paid_amount = 0;
            $newInvoice->remaining_amount = $invoice->total_amount;
            $newInvoice->pdf_path = null;
            $newInvoice->save();

            InvoiceLine::query()
                ->where('invoice_id', $invoice->getKey())
                ->get()
                ->each(function (InvoiceLine $line) use ($newInvoice): void {
                    $newLine = $line->replicate(['created_at', 'updated_at', 'deleted_at']);
                    $newLine->invoice_id = $newInvoice->getKey();
                    $newLine->save();
                });

            return $newInvoice->refresh();
        });
    }

    public function markPaid(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice): Invoice {
            $invoice->fill([
                'status' => 'paid',
                'paid_amount' => $invoice->total_amount,
                'remaining_amount' => 0,
            ]);
            $invoice->save();

            return $invoice->refresh();
        });
    }

    public function markSent(Invoice $invoice): Invoice
    {
        return $this->setStatus($invoice, 'sent');
    }

    public function markPartiallyPaid(Invoice $invoice): Invoice
    {
        return $this->setStatus($invoice, 'partially_paid');
    }

    public function markOverdue(Invoice $invoice): Invoice
    {
        return $this->setStatus($invoice, 'overdue');
    }

    public function cancel(Invoice $invoice): Invoice
    {
        return $this->setStatus($invoice, 'cancelled');
    }

    public function delete(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice): void {
            // TODO: Prevent deletion when invoice is posted to accounting or has reconciled payments.
            $invoice->delete();
        });
    }

    public function generateInvoiceNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('INV-%s-', $year);

        $lastNumber = Invoice::query()
            ->where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = is_string($lastNumber) ? ((int) substr($lastNumber, -6)) + 1 : 1;

        return sprintf('%s%06d', $prefix, $sequence);
    }

    public function generatePdf(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice): Invoice {
            // TODO: Generate a real invoice PDF and store it through Laravel storage.
            $invoice->fill([
                'pdf_path' => sprintf('invoices/%s.pdf', $invoice->invoice_number),
            ]);
            $invoice->save();

            return $invoice->refresh();
        });
    }

    private function syncLines(Invoice $invoice, array $lines): void
    {
        InvoiceLine::query()->where('invoice_id', $invoice->getKey())->delete();

        foreach ($lines as $index => $lineData) {
            if (! is_array($lineData)) {
                continue;
            }

            $quantity = (float) ($lineData['quantity'] ?? 1);
            $unitPrice = (float) ($lineData['unit_price'] ?? 0);
            $discount = (float) ($lineData['discount_amount'] ?? 0);
            $taxRate = (float) ($lineData['tax_rate'] ?? 0);
            $subtotal = max(0, ($quantity * $unitPrice) - $discount);
            $taxAmount = (float) ($lineData['tax_amount'] ?? round($subtotal * $taxRate / 100, 2));

            InvoiceLine::query()->create([
                'agency_id' => $invoice->agency_id,
                'invoice_id' => $invoice->getKey(),
                'description' => $lineData['description'] ?? '',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineData['line_total'] ?? round($subtotal + $taxAmount, 2),
                'display_order' => $lineData['display_order'] ?? $index,
            ]);
        }
    }

    private function createPayment(Invoice $invoice, array $paymentData, ?User $user = null): InvoicePayment
    {
        $paymentData = array_intersect_key($paymentData, array_flip([
            'financial_account_id',
            'received_by',
            'payment_number',
            'payment_date',
            'amount',
            'currency',
            'payment_method',
            'reference',
            'receipt_number',
            'receipt_path',
            'status',
            'notes',
        ]));

        $paymentData['agency_id'] = $invoice->agency_id;
        $paymentData['invoice_id'] = $invoice->getKey();
        $paymentData['received_by'] ??= $user?->getKey();
        $paymentData['payment_number'] ??= $this->generatePaymentNumber($invoice);
        $paymentData['payment_date'] ??= Carbon::now();
        $paymentData['currency'] ??= $invoice->currency ?? 'MAD';
        $paymentData['payment_method'] ??= 'cash';
        $paymentData['status'] ??= 'completed';

        return InvoicePayment::query()->create($paymentData);
    }

    private function recalculate(Invoice $invoice): Invoice
    {
        $lines = InvoiceLine::query()
            ->where('invoice_id', $invoice->getKey())
            ->get(['quantity', 'unit_price', 'discount_amount', 'tax_amount']);

        if ($lines->isNotEmpty()) {
            $subtotal = (float) $lines->sum(fn (InvoiceLine $line): float => (float) $line->quantity * (float) $line->unit_price);
            $discount = (float) $lines->sum('discount_amount');
            $tax = (float) $lines->sum('tax_amount');
            $total = max(0, $subtotal - $discount + $tax);
        } else {
            $subtotal = (float) $invoice->subtotal_amount;
            $discount = (float) $invoice->discount_amount;
            $tax = (float) $invoice->tax_amount;
            $total = (float) $invoice->total_amount;
        }

        $paid = (float) InvoicePayment::query()
            ->where('invoice_id', $invoice->getKey())
            ->whereNotIn('status', ['cancelled', 'failed', 'refunded'])
            ->sum('amount');

        $invoice->fill([
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'remaining_amount' => max(0, $total - $paid),
        ]);

        if ($paid > 0 && $paid < $total && ! in_array($invoice->status, ['cancelled', 'overdue'], true)) {
            $invoice->status = 'partially_paid';
        }

        if ($total > 0 && $paid >= $total) {
            $invoice->status = 'paid';
        }

        $invoice->save();

        return $invoice->refresh();
    }

    private function setStatus(Invoice $invoice, string $status): Invoice
    {
        return DB::transaction(function () use ($invoice, $status): Invoice {
            // TODO: Validate legal invoice status transitions and notification rules.
            $invoice->fill(['status' => $status]);
            $invoice->save();

            return $invoice->refresh();
        });
    }

    private function generatePaymentNumber(Invoice $invoice): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = sprintf('IPAY-%s-', $year);

        $count = InvoicePayment::query()
            ->where('agency_id', $invoice->agency_id)
            ->where('payment_number', 'like', $prefix . '%')
            ->count();

        return sprintf('%s%06d', $prefix, $count + 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function invoiceData(array $data): array
    {
        foreach (self::RELATION_KEYS as $key) {
            unset($data[$key]);
        }

        return array_intersect_key($data, array_flip([
            'agency_id',
            'client_id',
            'owner_id',
            'contract_id',
            'financial_transaction_id',
            'created_by',
            'invoice_number',
            'status',
            'issued_at',
            'due_at',
            'currency',
            'subtotal_amount',
            'discount_amount',
            'tax_amount',
            'total_amount',
            'paid_amount',
            'remaining_amount',
            'pdf_path',
            'notes',
        ]));
    }
}
