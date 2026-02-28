<?php

use Illuminate\Support\Facades\Route;

Route::livewire('create-invoice', 'pages::invoice.create')
    ->name('create-invoice')
    ->middleware(['auth', 'permission:Create:Invoice']);
