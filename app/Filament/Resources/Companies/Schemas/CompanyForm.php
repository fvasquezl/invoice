<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                TextEntry::make('company_logo_preview')
                    ->label('Logo actual')
                    ->state(fn ($record) => $record?->company_logo
                        ? new HtmlString('<img src="' . $record->company_logo . '" style="max-width: 50%; height: auto;"/>')
                        : new HtmlString('<span class="text-gray-400 text-sm">Sin logo</span>'))
                    ->html(),
                FileUpload::make('company_logo')
                    ->label(fn ($record) => $record?->company_logo ? 'Cambiar logo' : 'Logo')
                    ->image()
                    ->nullable()
                    ->getUploadedFileUsing(fn () => null)
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        $content = $file->getContent();
                        $mime = $file->getMimeType();
                        $file->delete();
                        return 'data:' . $mime . ';base64,' . base64_encode($content);
                    }),
            ]);
    }
}
