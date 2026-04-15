<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.app'), Title('Login - Ajeer Boost')] class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        session()->regenerate();

        $user = auth()->user();
        if ($user->isAdmin()) {
            return $this->redirectIntended('/admin');
        }

        return $this->redirectIntended('/dashboard');
    }
};
?>

<div class="flex flex-col items-center justify-center min-h-[calc(100vh-10rem)] px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="p-8">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Welcome back</h2>
            <p class="mt-2 text-slate-500">Sign in to manage your maintenance services.</p>

            <form wire:submit="login" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                        <input wire:model="email" type="email" id="email" required autofocus
                            class="mt-1 block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                        @error('email') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <input wire:model="password" type="password" id="password" required
                            class="mt-1 block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                        @error('password') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input wire:model="remember" type="checkbox" id="remember"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded transition duration-200">
                        <label for="remember" class="ml-2 block text-sm text-slate-600">Remember me</label>
                    </div>

                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition duration-200">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3 px-4 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-wait">
                    <span wire:loading.remove>Sign in</span>
                    <span wire:loading>Authenticating...</span>
                </button>
            </form>
        </div>

        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 text-center">
            <span class="text-sm text-slate-600">Don't have an account?</span>
            <a href="/register" class="ml-1 text-sm font-semibold text-indigo-600 hover:text-indigo-500 transition duration-200">
                Get a trial subscription
            </a>
        </div>
    </div>
</div>