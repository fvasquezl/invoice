<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use App\Models\Client;
use App\Models\Company;
use App\Models\Template;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoicePdfService;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;

new #[Layout('layouts.public')] class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];
    public ?int $selectedTemplateId = 1;

    public function mount(): void
    {
        $savedData = session()->pull('invoice_data');

        if ($savedData) {
            $this->form->fill($savedData);
        } else {
            $this->form->fill([
                'company_id'  => null,
                'client_id'   => null,
                'company_logo' => null,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'tax_rate' => 10,
                'items' => [
                    [
                        'description' => '',
                        'quantity' => 1,
                        'unit_price' => 0,
                    ]
                ],
            ]);
        }

        // handle pending action after auth redirect
        if (Auth::check()) {
            $pendingAction = session()->pull('pending_action');

            if ($pendingAction && $this->validateInvoiceData()) {
                if ($pendingAction === 'download') {
                    $this->handleDownload();
                } elseif ($pendingAction === 'email') {
                    $this->handleEmail();
                }
            }
        }
    }

    public function fillFromCompany(?int $id): void
    {
        if (!$id) {
            $this->form->fill(array_merge($this->data ?? [], [
                'company_id'      => null,
                'company_name'    => '',
                'company_address' => '',
                'company_email'   => '',
                'company_phone'   => '',
                'company_logo'    => null,
                'client_id'       => null,
                'client_name'     => '',
                'client_address'  => '',
                'client_email'    => '',
                'client_phone'    => '',
            ]));
            return;
        }

        $company = Company::find($id);
        if (!$company) {
            return;
        }

        $this->form->fill(array_merge($this->data ?? [], [
            'company_id'      => $id,
            'company_name'    => $company->company_name,
            'company_address' => $company->company_address ?? '',
            'company_email'   => $company->company_email ?? '',
            'company_phone'   => $company->company_phone ?? '',
            'company_logo'    => $company->company_logo,
            'template_id'     => $company->template_id,
            'client_id'       => null,
            'client_name'     => '',
            'client_address'  => '',
            'client_email'    => '',
            'client_phone'    => '',
        ]));
    }

    public function fillFromClient(?int $id): void
    {
        if (!$id) {
            $this->form->fill(array_merge($this->data ?? [], [
                'client_id'      => null,
                'client_name'    => '',
                'client_address' => '',
                'client_email'   => '',
                'client_phone'   => '',
            ]));
            return;
        }

        $client = Client::find($id);
        if (!$client) {
            return;
        }

        $this->form->fill(array_merge($this->data ?? [], [
            'client_id'      => $id,
            'client_name'    => $client->name,
            'client_address' => $client->address ?? '',
            'client_email'   => $client->email ?? '',
            'client_phone'   => $client->phone ?? '',
        ]));
    }

    public function lookupClientByEmail(?string $email): void
    {
        if (!$email || filled($this->data['client_id'] ?? null)) {
            return;
        }

        $client = Client::where('email', $email)->first();
        if (!$client) {
            return;
        }

        $this->form->fill(array_merge($this->data ?? [], [
            'client_id'      => $client->id,
            'client_name'    => $client->name,
            'client_address' => $client->address ?? '',
            'client_email'   => $client->email ?? '',
            'client_phone'   => $client->phone ?? '',
        ]));
    }

    // the form
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        // Company information
                        Section::make('Company Information')
                            ->columnSpanFull()
                            ->description('Your business details')
                            ->schema([
                                Select::make('company_id')
                                    ->label('Select Company')
                                    ->placeholder('Search a company...')
                                    ->options(fn () => Auth::check()
                                        ? (Auth::user()->hasRole('super_admin')
                                            ? Company::query()->pluck('company_name', 'id')
                                            : Auth::user()->companies->pluck('company_name', 'id'))
                                        : [])
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (?string $state) =>
                                        $this->fillFromCompany($state ? (int) $state : null))
                                    ->visible(fn () => Auth::check()
                                        && (Auth::user()->hasRole('super_admin')
                                            || Auth::user()->companies->isNotEmpty())),

                                Hidden::make('company_logo'),
                                Hidden::make('company_name'),

                                Textarea::make('company_address')
                                    ->label('Address')
                                    ->rows(3)
                                    ->placeholder('123 Business Street, City, Country')
                                    ->live(debounce: 500)
                                    ->readOnly(fn () => filled($this->data['company_id'] ?? null))
                                    ->extraInputAttributes(fn () => filled($this->data['company_id'] ?? null)
                                        ? ['class' => 'bg-gray-100 cursor-not-allowed']
                                        : []),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('company_email')
                                            ->label('Email')
                                            ->email()
                                            ->placeholder('hello@company.com')
                                            ->live(onBlur: true)
                                            ->readOnly(fn () => filled($this->data['company_id'] ?? null))
                                            ->extraInputAttributes(fn () => filled($this->data['company_id'] ?? null)
                                                ? ['class' => 'bg-gray-100 cursor-not-allowed']
                                                : []),

                                        TextInput::make('company_phone')
                                            ->label('Phone')
                                            ->tel()
                                            ->placeholder('+1 (555) 123-4567')
                                            ->live(onBlur: true)
                                            ->readOnly(fn () => filled($this->data['company_id'] ?? null))
                                            ->extraInputAttributes(fn () => filled($this->data['company_id'] ?? null)
                                                ? ['class' => 'bg-gray-100 cursor-not-allowed']
                                                : []),
                                    ]),
                            ])
                    ])->columnSpan(1),

                // Client Information
                Section::make('Client Information')
                    ->description('Bill to')
                    ->schema([
                        Select::make('client_id')
                            ->label('Select Client')
                            ->placeholder('Search or add new...')
                            ->options(fn () => Client::orderBy('email')
                                ->get()
                                ->groupBy(fn (Client $client) => $client->email ?: $client->name)
                                ->mapWithKeys(fn ($clients, string $key) => [
                                    $clients->first()->id => $key,
                                ])
                                ->all())
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(fn (?string $state) =>
                                $this->fillFromClient($state ? (int) $state : null))
                            ->visible(fn (Get $get) => filled($get('company_id'))),

                        TextInput::make('client_name')
                            ->label('Client Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Client Company Name')
                            ->live(debounce: 500)
                            ->readOnly(fn () => filled($this->data['client_id'] ?? null))
                            ->extraInputAttributes(fn () => filled($this->data['client_id'] ?? null)
                                ? ['class' => 'bg-gray-100 cursor-not-allowed']
                                : []),

                        Textarea::make('client_address')
                            ->label('Address')
                            ->rows(3)
                            ->placeholder('456 Client Avenue, City, Country')
                            ->live(debounce: 500)
                            ->readOnly(fn () => filled($this->data['client_id'] ?? null))
                            ->extraInputAttributes(fn () => filled($this->data['client_id'] ?? null)
                                ? ['class' => 'bg-gray-100 cursor-not-allowed']
                                : []),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('client_email')
                                    ->label('Email')
                                    ->email()
                                    ->placeholder('contact@client.com')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state) =>
                                        $this->lookupClientByEmail($state))
                                    ->readOnly(fn () => filled($this->data['client_id'] ?? null))
                                    ->extraInputAttributes(fn () => filled($this->data['client_id'] ?? null)
                                        ? ['class' => 'bg-gray-100 cursor-not-allowed']
                                        : []),

                                TextInput::make('client_phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->placeholder('+1 (555) 987-6543')
                                    ->live(debounce: 500)
                                    ->readOnly(fn () => filled($this->data['client_id'] ?? null))
                                    ->extraInputAttributes(fn () => filled($this->data['client_id'] ?? null)
                                        ? ['class' => 'bg-gray-100 cursor-not-allowed']
                                        : []),
                            ]),
                    ])
                    ->columnSpan(1),

                Grid::make(3)
                    ->schema([
                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->required()
                            ->default(now())
                            ->native(false),

                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(now()->addDays(30))
                            ->native(false),

                        TextInput::make('tax_rate')
                            ->label('Tax Rate (%)')
                            ->numeric()
                            ->default(18)
                            ->suffix('%')
                            ->live(onBlur: true),
                    ]),

                Section::make('Line Items')
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('description')
                                            ->label('Description')
                                            ->required()
                                            ->placeholder('Service or product description')
                                            ->columnSpan(2)
                                            ->live(debounce: 500),

                                        TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->minValue(1)
                                            ->live(onBlur: true)
                                            ->columnSpan(1),

                                        TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required()
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->columnSpan(1),
                                    ]),
                            ])
                            // ->defaultItems(1)
                            ->addActionLabel('Add Line Item')
                            ->reorderable()
                            ->cloneable()
                            ->deleteAction(
                                fn($action) => $action->requiresConfirmation()
                            ),
                    ]),

                Grid::make(2)
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Additional notes or special instructions')
                            ->columnSpan(1)
                            ->live(debounce: 500),

                        Textarea::make('terms')
                            ->label('Payment Terms')
                            ->rows(3)
                            ->placeholder('Payment is due within 30 days')
                            ->columnSpan(1)
                            ->live(debounce: 500),
                    ]),

                // Select::make('template_id')
                //     ->label('Invoice Template')
                //     ->options(Template::active()->pluck('name', 'id'))
                //     ->default(1)
                //     ->required()
                //     ->live()
                //     ->afterStateUpdated(function ($state) {
                //         $this->selectedTemplateId = false;
                //     })

            ])
            ->statePath('data');
    }

    public function getSubtotal()
    {
        $items = $this->data['items'] ?? [];

        return collect($items)->sum(function ($item) {
            return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
        });
    }

    public function getTaxAmount(): float
    {
        $taxRate = $this->data['tax_rate'] ?? 0;
        return $this->getSubtotal() * ($taxRate / 100);
    }

    public function getTotal(): float
    {
        return $this->getSubtotal() + $this->getTaxAmount();
    }

    public function getPreviewInvoice()
    {
        $data = $this->data;

        $previewCompany = new Company([
            'company_name'    => filled($data['company_name'] ?? '') ? $data['company_name'] : 'Your Company',
            'company_address' => $data['company_address'] ?? null,
            'company_email'   => $data['company_email']   ?? null,
            'company_phone'   => $data['company_phone']   ?? null,
            'company_logo'    => $data['company_logo']    ?? null,
            'template_id'     => $data['template_id']     ?? null,
        ]);

        $previewClient = new Client([
            'name'    => filled($data['client_name'] ?? '') ? $data['client_name'] : 'Client Name',
            'address' => $data['client_address'] ?? null,
            'email'   => $data['client_email']   ?? null,
            'phone'   => $data['client_phone']   ?? null,
        ]);

        $invoice = new Invoice([
            'invoice_number' => 'INV-' . now()->year . '-XXXX',
            'invoice_date' => isset($data['invoice_date']) ? Carbon\Carbon::parse($data['invoice_date']) : now(),
            'due_date' => isset($data['due_date']) ? Carbon\Carbon::parse($data['due_date']) : now()->addDays(30),
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
            'subtotal' => $this->getSubtotal(),
            'tax_rate' => $data['tax_rate'] ?? 0,
            'tax_amount' => $this->getTaxAmount(),
            'total' => $this->getTotal(),
            'template_id' => $data['template_id'] ?? 1,
        ]);

        $invoice->setRelation('company', $previewCompany);
        $invoice->setRelation('client', $previewClient);

        //set the template relationship
        $templateId = $data['template_id'] ?? null;
        $template = $templateId ? Template::find($templateId) : Template::active()->first();
        $invoice->setRelation('template', $template);

        // create temporary invoice items
        $items = collect($data['items'] ?? [])->map(function ($item, $index) {
            return new InvoiceItem([
                'description' => $item['description'] ?? '',
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'total' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                'sort_order' => $index,
            ]);
        });

        $invoice->setRelation('items', $items);

        return $invoice;
    }

    protected function createInvoice(): Invoice
    {
        $data = $this->data;

        $clientId = $data['client_id'] ?? null;

        if (!$clientId && !empty($data['client_name'])) {
            $email = !empty($data['client_email']) ? $data['client_email'] : null;

            $client = $email
                ? Client::where('email', $email)->first()
                : Client::where('name', $data['client_name'])->first();

            if (!$client) {
                $client = Client::create([
                    'name'    => $data['client_name'],
                    'address' => $data['client_address'] ?? null,
                    'email'   => $email,
                    'phone'   => $data['client_phone'] ?? null,
                ]);
            }

            if (!empty($data['company_id'])) {
                $client->companies()->syncWithoutDetaching([$data['company_id']]);
            }

            $clientId = $client->id;
        }

        $invoice = Invoice::create([
            'user_id'        => Auth::id(),
            'company_id'     => $data['company_id'] ?? null,
            'client_id'      => $clientId,
            'invoice_number' => (new Invoice())->generateInvoiceNumber(),
            'invoice_date'   => $data['invoice_date'],
            'due_date'       => $data['due_date'],
            'notes'          => $data['notes'] ?? null,
            'terms'          => $data['terms'] ?? null,
            'subtotal'       => $this->getSubtotal(),
            'tax_rate'       => $data['tax_rate'] ?? 0,
            'tax_amount'     => $this->getTaxAmount(),
            'total'          => $this->getTotal(),
            'template_id'    => $data['template_id'] ?? Template::active()->value('id'),
            'status'         => 'draft',
        ]);

        foreach ($data['items'] ?? [] as $index => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
                'sort_order' => $index,
            ]);
        }

        return $invoice;
    }

    protected function validateInvoiceData(): bool
    {
        $data = $this->data;

        if (empty($data['company_name']) || empty($data['client_name'])) {
            return false;
        }

        if (empty($data['invoice_date']) || empty($data['due_date'])) {
            return false;
        }

        $items = $data['items'] ?? [];
        if (empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (empty($item['description']) || empty($item['quantity'] || empty($item['unit_price']))) {
                return false;
            }
        }

        return true;
    }

    #[On('auth-success')]
    public function handleAuthSuccess(): void
    {
        $pendingAction = session()->pull('pending_action');
        $invoiceData = session()->pull('invoice_data');

        if ($invoiceData) {
            $this->data = $invoiceData;
        }

        if ($pendingAction && $this->validateInvoiceData()) {
            if ($pendingAction === 'download') {
                $this->handleDownload();
            } elseif ($pendingAction === 'email') {
                $this->handleEmail();
            }
        }

    }

    public function with(): array
    {
        return [
            'title' => 'Create Invoice',
            'subtotal' => $this->getSubtotal(),
            'taxAmount' => $this->getTaxAmount(),
            'total' => $this->getTotal(),
            'previewInvoice' => $this->getPreviewInvoice(),
        ];
    }

    public function handleDownload(): void
    {

        if (!$this->validateInvoiceData()) {
            $this->form->validate();
            return;
        }

        if (!Auth::check()) {
            session()->put('pending_action', 'download');
            session()->put('invoice_data', $this->data);
            $this->dispatch('open-auth-modal', mode: 'register');
            return;
        }

        $invoice = $this->createInvoice();
        $this->dispatch('notify', message: 'Preparing download...');

        $this->redirect(route('invoice.download', $invoice), navigate: false);
    }

    public function handleEmail(): void
    {
        if (!$this->validateInvoiceData()) {
            $this->form->validate();
            return;
        }

        if (!Auth::check()) {
            session()->put('pending_action', 'email');
            session()->put('invoice_data', $this->data);
            $this->dispatch('open-auth-modal', mode: 'register');
            return;
        }

        $invoice = $this->createInvoice();
        $this->dispatch('notify', message: 'Sending email...');
        // validate client email exists
        if (!$invoice->client_email) {
            $this->dispatch('notify', message: 'Client email is required to send invoice');
            return;
        }

        // send mail
        Mail::to($invoice->client_email)->queue(new InvoiceMail($invoice));

        // clean up temp file
        $tempPath = storage_path('app/temp/invoice-' . $invoice->invoice_number . '.pdf');
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        // update invoice status
        $invoice->update(['status' => 'sent']);

        $this->dispatch('notify', message: 'Invoice sent successfully to ' . $invoice->client_email);
    }

    public function handlePrint(): void
    {
        if (!Auth::check()) {
            session()->put('pending_action', 'print');
            session()->put('invoice_data', $this->data);
            $this->dispatch('open-auth-modal', mode: 'register');
            return;
        }

        $invoice = $this->createInvoice();

        $this->dispatch('open-print-window', url: route('invoice.print', $invoice));
    }
};
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        @auth
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Dashboard
            </a>
        @endauth
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Create Invoice</h1>
        <p class="text-gray-600">Fill in the details below to generate your professional invoice</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- left column: form + summary --}}
        <div class="space-y-6 order-1 xl:order-0">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form wire:submit="save">
                    {{ $this->form }}
                </form>
            </div>

            {{-- Totals Summary --}}
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                <h3 class="text-lg font-semibold mb-4">Summary</h3>
                <div wire:loading class="text-sm text-gray-500">Calculating...</div>
                <div class="space-y-2">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal:</span>
                        <span class="font-semibold">${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Tax ({{ $this->data['tax_rate'] ?? 0 }}%):</span>
                        <span class="font-semibold">${{ number_format($taxAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                        <span>Total:</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Actions + Preview --}}
        <div class="space-y-6 xl:order-0 xl:sticky xl:top-6 xl:self-start">

            {{-- Actions --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" wire:click="handleDownload" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="flex flex-col items-center justify-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-2 rounded-lg transition text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span wire:loading.remove wire:target="handleDownload">Download</span>
                        <span wire:loading wire:target="handleDownload">...</span>
                    </button>
                    <button type="button" wire:click="handlePrint" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="flex flex-col items-center justify-center gap-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-2 rounded-lg transition text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span wire:loading.remove wire:target="handlePrint">Print</span>
                        <span wire:loading wire:target="handlePrint">...</span>
                    </button>
                    <button type="button" wire:click="handleEmail" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="flex flex-col items-center justify-center gap-1 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-2 rounded-lg border-2 border-gray-300 transition text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span wire:loading.remove wire:target="handleEmail">Email</span>
                        <span wire:loading wire:target="handleEmail">...</span>
                    </button>
                </div>
            </div>

            {{-- preview --}}
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">Live Preview</h3>
                    <span class="text-xs text-gray-500">Updates as you type</span>
                </div>

                <div class="p-4 bg-gray-100 relative">
                    {{-- Loading Overlay --}}
                    <div wire:loading wire:target="data.company_name,data.client_name,data.items"
                         class="absolute inset-0 bg-white/50 backdrop-blur-sm flex items-center justify-center z-10 rounded">
                        <div class="bg-white rounded-lg shadow-lg px-4 py-2">
                            <span class="text-sm text-gray-600">Updating preview...</span>
                        </div>
                    </div>
                    <div class="bg-white rounded shadow-sm" style="transform: scale(0.85); transform-origin: top;">
                        <x-invoice-renderer :invoice="$previewInvoice" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6" x-data
         x-on:open-print-window.window="window.open($event.detail.url, '_blank', 'width=1024,height=768')"></div>

</div>
