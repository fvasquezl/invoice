<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
                TextInput::make('name')
                    ->required(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextEntry::make('logo_preview')
                    ->label('Logo actual')
                    ->state(fn ($record) => $record?->logo
                        ? new HtmlString('<img src="'.$record->logo.'" style="max-width: 50%; height: auto;"/>')
                        : new HtmlString('<span class="text-gray-400 text-sm">Sin logo</span>'))
                    ->html(),
                FileUpload::make('logo')
                    ->label(fn ($record) => $record?->logo ? 'Cambiar logo' : 'Logo')
                    ->image()
                    ->nullable()
                    ->getUploadedFileUsing(fn () => null)
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        $content = $file->getContent();
                        $mime = $file->getMimeType();
                        $file->delete();

                        return 'data:'.$mime.';base64,'.base64_encode($content);
                    }),
                Select::make('template_id')
                    ->label('Invoice Template')
                    ->relationship('template', 'name')
                    ->nullable()
                    ->placeholder('Select a template'),
            ]);
    }
}
