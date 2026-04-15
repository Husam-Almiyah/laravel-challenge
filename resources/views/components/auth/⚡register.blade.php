<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new #[Layout('layouts.app'), Title('Register - Ajeer Boost')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'role' => 'client', // Default role for new registrations
        ]);

        Auth::login($user);

        return $this->redirect('/dashboard');
    }
};
?>

<div class="flex flex-col items-center justify-center min-h-[calc(100vh-10rem)] px-4 py-12">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="p-8">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Create your account</h2>
            <p class="mt-2 text-slate-500">Get your trial subscription and start browsing services.</p>

            <form wire:submit="register" class="mt-8 space-y-5">
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Full Name</label>
                        <input wire:model="name" type="text" id="name" required autofocus
                            class="mt-1 block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                        @error('name') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                            <input wire:model="email" type="email" id="email" required
                                class="mt-1 block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                            @error('email') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700">Phone Number</label>
                            <input wire:model="phone" type="text" id="phone" required
                                class="mt-1 block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                            @error('phone') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <input wire:model="password" type="password" id="password" required
                            class="mt-1 block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                        @error('password') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm Password</label>
                        <input wire:model="password_confirmation" type="password" id="password_confirmation" required
                            class="mt-1 block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" type="checkbox" required
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded transition duration-200">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="text-slate-600">I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Terms of Service</a> and <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Privacy Policy</a>.</label>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3 px-4 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-wait">
                    <span wire:loading.remove>Create Account & Get Trial</span>
                    <span wire:loading>Creating Account...</span>
                </button>
            </form>
        </div>

        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 text-center">
            <span class="text-sm text-slate-600">Already have an account?</span>
            <a href="/login" class="ml-1 text-sm font-semibold text-indigo-600 hover:text-indigo-500 transition duration-200">
                Sign in here
            </a>
        </div>
    </div>
</div>