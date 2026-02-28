<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('companies')
                    ->label('Companies')
                    ->multiple()
                    ->relationship('companies', 'company_name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('address')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->email()
                    ->unique(table: \App\Models\Client::class, column: 'email', ignoreRecord: true),
                TextInput::make('phone')
                    ->tel(),
            ]);
    }
}
