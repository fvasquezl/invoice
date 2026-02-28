<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'client_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'notes',
        'terms',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'status',
        'template_id',
        'pdf_path',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getCompanyNameAttribute(): string
    {
        return $this->company?->name ?? '';
    }

    public function getCompanyAddressAttribute(): ?string
    {
        return $this->company?->address;
    }

    public function getCompanyEmailAttribute(): ?string
    {
        return $this->company?->email;
    }

    public function getCompanyPhoneAttribute(): ?string
    {
        return $this->company?->phone;
    }

    public function getCompanyLogoAttribute(): ?string
    {
        return $this->company?->logo;
    }

    public function getClientNameAttribute(): string
    {
        return $this->client?->name ?? '';
    }

    public function getClientAddressAttribute(): ?string
    {
        return $this->client?->address;
    }

    public function getClientEmailAttribute(): ?string
    {
        return $this->client?->email;
    }

    public function getClientPhoneAttribute(): ?string
    {
        return $this->client?->phone;
    }

    // helper method to calculate totals
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total');
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->total = $this->subtotal + $this->tax_amount;
        $this->save();
    }

    public function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return 'INV-'.$year.'-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
