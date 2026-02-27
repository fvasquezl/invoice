<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.public', ['title' => 'Dashboard'])] class extends Component {
    use WithPagination;

    public string $filter = 'all';
    public string $search  = '';

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: false);
        }
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function invoices()
    {
        $query = Invoice::where('user_id', Auth::id())->latest();

        if ($this->filter !== 'all') {
            if ($this->filter === 'draft') {
                $query->where(fn ($q) => $q->where('status', 'draft')->orWhereNull('status'));
            } else {
                $query->where('status', $this->filter);
            }
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(fn ($q) => $q
                ->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
            );
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function stats(): array
    {
        $userId = Auth::id();

        return [
            'total'      => Invoice::where('user_id', $userId)->count(),
            'revenue'    => Invoice::where('user_id', $userId)->sum('total'),
            'this_month' => Invoice::where('user_id', $userId)
                                ->whereMonth('invoice_date', now()->month)
                                ->whereYear('invoice_date', now()->year)
                                ->count(),
            'draft'      => Invoice::where('user_id', $userId)
                                ->where(fn ($q) => $q->where('status', 'draft')->orWhereNull('status'))
                                ->count(),
        ];
    }
};
?>

<div>
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Invoices</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and track all your invoices</p>
        </div>
        <a href="{{ route('create-invoice') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Invoice
        </a>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Total Invoices</p>
            <p class="text-3xl font-bold text-gray-900">{{ $this->stats['total'] }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Total Revenue</p>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($this->stats['revenue'], 2) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">This Month</p>
            <p class="text-3xl font-bold text-gray-900">{{ $this->stats['this_month'] }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Drafts</p>
            <p class="text-3xl font-bold text-gray-900">{{ $this->stats['draft'] }}</p>
        </div>

    </div>

    {{-- Filters + Search --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">

            {{-- Status tabs --}}
            <div class="flex gap-1 bg-gray-100 rounded-lg p-1 self-start">
                @foreach(['all' => 'All', 'draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid'] as $key => $label)
                    <button
                        wire:click="setFilter('{{ $key }}')"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition
                               {{ $filter === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by invoice # or client..."
                    class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent w-72"
                >
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-400 bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Due Date</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->invoices as $invoice)
                        @php
                            $status  = $invoice->status ?? 'draft';
                            $overdue = $status === 'draft' && $invoice->due_date?->isPast();
                            $badge   = match(true) {
                                $overdue          => 'bg-red-100 text-red-700',
                                $status === 'paid' => 'bg-green-100 text-green-700',
                                $status === 'sent' => 'bg-blue-100 text-blue-700',
                                default            => 'bg-gray-100 text-gray-600',
                            };
                            $label   = $overdue ? 'Overdue' : ucfirst($status);
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 font-mono font-semibold text-gray-900">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $invoice->client_name }}</div>
                                @if($invoice->client_email)
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $invoice->client_email }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $invoice->invoice_date?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 {{ $overdue ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                {{ $invoice->due_date?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-gray-900">
                                ${{ number_format($invoice->total, 2) }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('invoice-preview', $invoice) }}"
                                   class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 font-medium text-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-sm font-medium">No invoices found</p>
                                    @if($search || $filter !== 'all')
                                        <button wire:click="$set('search', ''); setFilter('all')"
                                                class="text-xs text-blue-600 hover:text-blue-700 underline">
                                            Clear filters
                                        </button>
                                    @else
                                        <a href="{{ route('create-invoice') }}"
                                           class="text-xs text-blue-600 hover:text-blue-700 underline">
                                            Create your first invoice
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($this->invoices->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $this->invoices->links() }}
            </div>
        @endif

    </div>
</div>
