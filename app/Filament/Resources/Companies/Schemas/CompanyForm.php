<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->required(),
                Textarea::make('company_address')
                    ->columnSpanFull(),
                TextInput::make('company_email')
                    ->email(),
                TextInput::make('company_phone')
                    ->tel(),
                FileUpload::make('company_logo')
                    ->image()
                    ->nullable(),
            ]);
    }
}
