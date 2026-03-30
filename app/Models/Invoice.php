<?php

namespace App\Models;

use App\InvoiceStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_id',
    'invoice_number',
    'status',
    'issued_at',
    'due_at',
    'sent_at',
    'paid_at',
    'archived_at',
    'subtotal_cents',
    'tax_total_cents',
    'total_cents',
    'currency',
    'notes',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'issued_at' => 'immutable_datetime',
            'due_at' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'paid_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'subtotal_cents' => 'integer',
            'tax_total_cents' => 'integer',
            'total_cents' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->invoiceItems();
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidAmountCents(): int
    {
        if ($this->relationLoaded('payments')) {
            /** @var Collection<int, Payment> $payments */
            $payments = $this->getRelation('payments');

            return (int) $payments->sum('amount_cents');
        }

        return (int) $this->payments()->sum('amount_cents');
    }

    public function balanceDueCents(): int
    {
        return max($this->total_cents - $this->paidAmountCents(), 0);
    }

    public function isCancelled(): bool
    {
        return $this->status === InvoiceStatus::Cancelled;
    }

    public function isArchived(): bool
    {
        return $this->status === InvoiceStatus::Archived;
    }

    public function isTerminal(): bool
    {
        return $this->status instanceof InvoiceStatus
            ? $this->status->isTerminal()
            : false;
    }
}
