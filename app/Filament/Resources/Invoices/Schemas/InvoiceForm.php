<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->disabled(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('company_id')
                    ->relationship('company', 'company_name'),
                Select::make('template_id')
                    ->relationship('template', 'name'),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->label('Client')
                    ->searchable()
                    ->preload(),
                DatePicker::make('invoice_date')->required(),
                DatePicker::make('due_date'),
                TextInput::make('tax_rate')
                    ->numeric()
                    ->suffix('%')
                    ->default(0),
                Textarea::make('notes')->rows(3),
                Textarea::make('terms')->rows(3),
            ]);
    }
}
