<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.public', ['title' => 'Sign In'])] class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('dashboard'), navigate: false);
        }
    }

    public function login(): void
    {
        $this->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            $this->redirect(route('dashboard'), navigate: false);
        } else {
            $this->addError('email', 'The provided credentials do not match our records.');
        }
    }
};
?>

<div class="min-h-[70vh] flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-md w-full max-w-md p-8">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
            <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
        </div>

        <form wire:submit="login" class="space-y-5">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email address
                </label>
                <input
                    type="email"
                    id="email"
                    wire:model="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="you@example.com"
                    autofocus
                >
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Password
                </label>
                <input
                    type="password"
                    id="password"
                    wire:model="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="••••••••"
                >
                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model="remember"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    Remember me
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
            >
                <span wire:loading.remove>Sign In</span>
                <span wire:loading>Signing in...</span>
            </button>

        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Don't have an account?
            <button
                type="button"
                onclick="Livewire.dispatch('open-auth-modal', { mode: 'register' })"
                class="text-blue-600 hover:text-blue-700 font-medium"
            >
                Sign up
            </button>
        </p>

    </div>
</div>
