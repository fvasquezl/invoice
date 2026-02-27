@props(['invoice', 'forPdf' => false])

@php
    $primaryColor = $invoice->template->settings['primary_color'] ?? '#0891b2';
    $logoSrc = $invoice->company_logo
        ? (str_starts_with($invoice->company_logo, 'data:')
            ? $invoice->company_logo
            : Storage::url($invoice->company_logo))
        : null;
    $logoSrcPdf = $invoice->company_logo
        ? (str_starts_with($invoice->company_logo, 'data:')
            ? $invoice->company_logo
            : storage_path('app/public/' . $invoice->company_logo))
        : null;
@endphp

@if($forPdf)
{{-- PDF Version --}}
<div style="font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #1f2937;">

    {{-- Blue top bar --}}
    <div style="background-color: {{ $primaryColor }}; padding: 28px 40px;">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: middle; color: #ffffff;">
                    <div style="font-size: 13px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; opacity: 0.85;">
                        Invoice
                    </div>
                    <div style="font-size: 26px; font-weight: bold; margin-top: 4px;">
                        {{ $invoice->invoice_number }}
                    </div>
                </td>
                <td style="text-align: right; vertical-align: middle; color: #ffffff;">
                    <div style="font-size: 10px; opacity: 0.8; margin-bottom: 4px;">Issued</div>
                    <div style="font-size: 14px; font-weight: 600;">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                    <div style="font-size: 10px; opacity: 0.8; margin-top: 8px; margin-bottom: 4px;">Due</div>
                    <div style="font-size: 14px; font-weight: 600;">{{ $invoice->due_date->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Company + client row --}}
    <table style="width: 100%; padding: 32px 40px 0 40px;">
        <tr>
            {{-- From --}}
            <td style="width: 50%; vertical-align: top; padding: 32px 20px 0 40px;">
                @if($logoSrcPdf)
                    <img src="{{ $logoSrcPdf }}"
                         alt="{{ $invoice->company_name }}"
                         style="height: 72px; margin-bottom: 14px; object-fit: contain; display: block;">
                @endif
                <div style="font-size: 16px; font-weight: bold; color: #1f2937; margin-bottom: 6px;">
                    {{ $invoice->company_name }}
                </div>
                <div style="font-size: 12px; color: #4b5563; line-height: 1.6;">
                    @if($invoice->company_address)
                        <div>{{ $invoice->company_address }}</div>
                    @endif
                    @if($invoice->company_email)
                        <div>{{ $invoice->company_email }}</div>
                    @endif
                    @if($invoice->company_phone)
                        <div>{{ $invoice->company_phone }}</div>
                    @endif
                </div>
            </td>
            {{-- Bill To --}}
            <td style="width: 50%; vertical-align: top; padding: 32px 40px 0 20px;">
                <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: {{ $primaryColor }}; margin-bottom: 10px;">
                    Bill To
                </div>
                <div style="font-size: 15px; font-weight: bold; color: #1f2937; margin-bottom: 6px;">
                    {{ $invoice->client_name }}
                </div>
                <div style="font-size: 12px; color: #4b5563; line-height: 1.6;">
                    @if($invoice->client_address)
                        <div>{{ $invoice->client_address }}</div>
                    @endif
                    @if($invoice->client_email)
                        <div>{{ $invoice->client_email }}</div>
                    @endif
                    @if($invoice->client_phone)
                        <div>{{ $invoice->client_phone }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Line Items --}}
    <div style="padding: 32px 40px 0 40px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: {{ $primaryColor }};">
                    <th style="text-align: left; padding: 10px 14px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #ffffff;">
                        Description
                    </th>
                    <th style="text-align: center; padding: 10px 14px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; width: 60px;">
                        Qty
                    </th>
                    <th style="text-align: right; padding: 10px 14px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; width: 100px;">
                        Rate
                    </th>
                    <th style="text-align: right; padding: 10px 14px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; width: 110px;">
                        Amount
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr style="background-color: {{ $loop->even ? '#f0f9ff' : '#ffffff' }}; border-bottom: 1px solid #e0f2fe;">
                        <td style="padding: 11px 14px; color: #1f2937;">{{ $item->description }}</td>
                        <td style="padding: 11px 14px; text-align: center; color: #4b5563;">{{ $item->quantity }}</td>
                        <td style="padding: 11px 14px; text-align: right; color: #4b5563;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="padding: 11px 14px; text-align: right; font-weight: 600; color: #1f2937;">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <table style="width: 100%; padding: 0 40px;">
        <tr>
            <td style="width: 55%; padding: 20px 0 20px 40px;"></td>
            <td style="width: 45%; padding: 20px 40px 20px 0;">
                <table style="width: 100%; font-size: 13px;">
                    <tr style="border-bottom: 1px solid #e0f2fe;">
                        <td style="padding: 8px 0; color: #4b5563;">Subtotal</td>
                        <td style="padding: 8px 0; text-align: right; font-weight: 600;">${{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e0f2fe;">
                        <td style="padding: 8px 0; color: #4b5563;">Tax ({{ $invoice->tax_rate }}%)</td>
                        <td style="padding: 8px 0; text-align: right; font-weight: 600;">${{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    <tr style="background-color: {{ $primaryColor }}; color: #ffffff;">
                        <td style="padding: 12px 14px; font-size: 15px; font-weight: bold;">Total</td>
                        <td style="padding: 12px 14px; text-align: right; font-size: 18px; font-weight: bold;">${{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Notes & Terms --}}
    @if($invoice->notes || $invoice->terms)
        <div style="padding: 0 40px 40px 40px;">
            @if($invoice->notes)
                <div style="background-color: #f0f9ff; border-left: 4px solid {{ $primaryColor }}; padding: 14px 18px; margin-bottom: 12px;">
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: {{ $primaryColor }}; margin-bottom: 6px;">Notes</div>
                    <p style="color: #374151; font-size: 12px;">{{ $invoice->notes }}</p>
                </div>
            @endif
            @if($invoice->terms)
                <div style="background-color: #f0f9ff; border-left: 4px solid {{ $primaryColor }}; padding: 14px 18px;">
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: {{ $primaryColor }}; margin-bottom: 6px;">Payment Terms</div>
                    <p style="color: #374151; font-size: 12px;">{{ $invoice->terms }}</p>
                </div>
            @endif
        </div>
    @endif

</div>

@else
{{-- Browser Version --}}
<div class="corporate-blue bg-white" style="min-height: 297mm;">

    {{-- Blue top bar --}}
    <div class="px-12 py-7" style="background-color: {{ $primaryColor }}">
        <div class="flex justify-between items-center text-white">
            <div>
                <div class="text-xs font-semibold tracking-widest uppercase opacity-80">Invoice</div>
                <div class="text-3xl font-bold mt-1">{{ $invoice->invoice_number }}</div>
            </div>
            <div class="text-right text-sm">
                <div class="opacity-75 text-xs mb-1">Issued</div>
                <div class="font-semibold">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                <div class="opacity-75 text-xs mt-3 mb-1">Due</div>
                <div class="font-semibold">{{ $invoice->due_date->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Company + client --}}
    <div class="grid grid-cols-2 gap-0 px-12 pt-10 pb-8 border-b border-gray-100">

        {{-- From: logo + company info --}}
        <div class="pr-10 border-r border-gray-100">
            @if($logoSrc)
                <img src="{{ $logoSrc }}"
                     alt="{{ $invoice->company_name }}"
                     class="h-20 mb-4 object-contain">
            @endif
            <div class="text-lg font-bold text-gray-900 mb-1">{{ $invoice->company_name }}</div>
            <div class="text-sm text-gray-500 space-y-0.5">
                @if($invoice->company_address)
                    <p>{{ $invoice->company_address }}</p>
                @endif
                @if($invoice->company_email)
                    <p>{{ $invoice->company_email }}</p>
                @endif
                @if($invoice->company_phone)
                    <p>{{ $invoice->company_phone }}</p>
                @endif
            </div>
        </div>

        {{-- Bill To --}}
        <div class="pl-10">
            <div class="text-xs font-bold uppercase tracking-wider mb-3"
                 style="color: {{ $primaryColor }}">
                Bill To
            </div>
            <div class="text-lg font-bold text-gray-900 mb-1">{{ $invoice->client_name }}</div>
            <div class="text-sm text-gray-500 space-y-0.5">
                @if($invoice->client_address)
                    <p>{{ $invoice->client_address }}</p>
                @endif
                @if($invoice->client_email)
                    <p>{{ $invoice->client_email }}</p>
                @endif
                @if($invoice->client_phone)
                    <p>{{ $invoice->client_phone }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="px-12 pt-8 pb-6">
        <table class="w-full">
            <thead>
                <tr style="background-color: {{ $primaryColor }}">
                    <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wide text-white">
                        Description
                    </th>
                    <th class="text-center py-3 px-4 text-xs font-semibold uppercase tracking-wide text-white w-16">
                        Qty
                    </th>
                    <th class="text-right py-3 px-4 text-xs font-semibold uppercase tracking-wide text-white w-28">
                        Rate
                    </th>
                    <th class="text-right py-3 px-4 text-xs font-semibold uppercase tracking-wide text-white w-32">
                        Amount
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr class="{{ $loop->even ? 'bg-sky-50' : 'bg-white' }} border-b border-sky-100">
                        <td class="py-3 px-4 text-gray-800">{{ $item->description }}</td>
                        <td class="py-3 px-4 text-center text-gray-500">{{ $item->quantity }}</td>
                        <td class="py-3 px-4 text-right text-gray-500">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-800">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="flex justify-end px-12 pb-8">
        <div class="w-80">
            <div class="flex justify-between py-2 text-sm text-gray-600 border-b border-sky-100">
                <span>Subtotal</span>
                <span class="font-semibold">${{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 text-sm text-gray-600 border-b border-sky-100">
                <span>Tax ({{ $invoice->tax_rate }}%)</span>
                <span class="font-semibold">${{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 mt-1 text-white font-bold"
                 style="background-color: {{ $primaryColor }}">
                <span class="text-base">Total</span>
                <span class="text-xl">${{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Notes & Terms --}}
    @if($invoice->notes || $invoice->terms)
        <div class="px-12 pb-12 space-y-4">
            @if($invoice->notes)
                <div class="border-l-4 bg-sky-50 px-5 py-4" style="border-color: {{ $primaryColor }}">
                    <h4 class="text-xs font-bold uppercase tracking-wider mb-2"
                        style="color: {{ $primaryColor }}">Notes</h4>
                    <p class="text-sm text-gray-700">{{ $invoice->notes }}</p>
                </div>
            @endif
            @if($invoice->terms)
                <div class="border-l-4 bg-sky-50 px-5 py-4" style="border-color: {{ $primaryColor }}">
                    <h4 class="text-xs font-bold uppercase tracking-wider mb-2"
                        style="color: {{ $primaryColor }}">Payment Terms</h4>
                    <p class="text-sm text-gray-700">{{ $invoice->terms }}</p>
                </div>
            @endif
        </div>
    @endif

</div>
@endif
