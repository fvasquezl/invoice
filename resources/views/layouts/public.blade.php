<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Invoice Generator' }}</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @filamentStyles
    @livewireStyles
</head>

<body class="bg-gray-50 antialiased">
<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-900">Invoice Generator</h1>
            </div>
            <div class="flex items-center space-x-4">
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button
                            @click="open = !open"
                            @click.outside="open = false"
                            class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 font-medium focus:outline-none"
                        >
                            <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold select-none">
                                {{ collect(explode(' ', Auth::user()->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('') }}
                            </span>
                            <span class="hidden sm:block">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50"
                            style="display: none;"
                        >
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ Auth::user()->email }}</p>
                                @php $role = Auth::user()->getRoleNames()->first() @endphp
                                @if($role)
                                    <span class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ str_replace('_', ' ', ucfirst($role)) }}
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h7v7H3zM14 7h7v7h-7zM3 17h7v4H3zM14 17h7v4h-7z"/>
                                </svg>
                                Dashboard
                            </a>

                            <a href="{{ route('create-invoice') }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition border-b border-gray-100">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Invoice
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                                >
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                       class="text-gray-700 hover:text-gray-900 font-medium text-sm">
                        Sign In
                    </a>
                    <button type="button" onclick="Livewire.dispatch('open-auth-modal', { mode: 'register' })"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm">
                        Sign Up
                    </button>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{ $slot }}
</main>

@filamentScripts
@livewireScripts
{{-- Auth modal --}}
<livewire:auth-modal/>
<div x-data="{
            show: false,
            message: '',
            showNotification(msg) {
                this.message = msg;
                this.show = true;
                setTimeout(() => this.show = false, 3000);
            }
        }"
     x-on:notify.window="showNotification($event.detail.message)"
     x-init="@if(session('notify')) showNotification('{{ session('notify') }}') @endif"
     x-show="show" x-transition
     class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50"
     style="display: none;" x-text="message">
</div>
</body>

</html>
