@props(['invoice', 'forPdf' => false])

@php
    $primaryColor = $invoice->template->settings['primary_color'] ?? '#2563eb';
@endphp

<div class="modern-minimalist bg-white p-12" style="min-height: 297mm;">
    {{-- Header Section --}}
    <div class="flex justify-between items-start mb-12">
        {{-- Company Info --}}
        <div class="flex-1">
            @if ($invoice->company_logo)
                <img src="{{ Storage::url($invoice->company_logo) }}" alt="{{ $invoice->company_name }}" class="h-16 mb-4">
            @endif

            <h1 class="text-3xl font-bold mb-2" style="color: {{ $primaryColor }}">
                {{ $invoice->company_name }}
            </h1>

            <div class="text-gray-600 text-sm space-y-1">
                @if ($invoice->company_address)
                    <p>{{ $invoice->company_address }}</p>
                @endif
                @if ($invoice->company_email)
                    <p>{{ $invoice->company_email }}</p>
                @endif
                @if ($invoice->company_phone)
                    <p>{{ $invoice->company_phone }}</p>
                @endif
            </div>
            {{-- Invoice Details --}}
            <div class="text-right">
                <h2 class="text-4xl font-bold mb-6" style="color: {{ $primaryColor }}">
                    INVOICE
                </h2>
                <div class="text-sm space-y-2">
                    <div>
                        <span class="text-gray-600">Invoice Number: </span>
                        <strong class="ml-2">{{ $invoice->invoice_number }}</strong>
                    </div>
                    <div>
                        <div>
                            <span class="text-gray-600">Invoice Date: </span>
                            <strong class="ml-2">{{ $invoice->invoice_date->format('M d, Y') }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-600">Due Date: </span>
                            <strong class="ml-2">{{ $invoice->due_date->format('M d, Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Bill To Section --}}

    </div>
