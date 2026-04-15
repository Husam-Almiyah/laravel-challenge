<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Domains\Booking\Models\Cart;
use App\Domains\Booking\Models\CartItem;

new #[Layout('layouts.app'), Title('My Shopping Cart - Ajeer Boost')] class extends Component
{
    public function getItemsProperty()
    {
        return auth()->user()->cart?->items ?? collect();
    }

    public function getTotalProperty()
    {
        return $this->items->sum(fn($item) => $item->price * $item->quantity);
    }

    public function increment($itemId)
    {
        CartItem::where('id', $itemId)->increment('quantity');
        $this->dispatch('cart-updated');
    }

    public function decrement($itemId)
    {
        $item = CartItem::where('id', $itemId)->first();
        if ($item->quantity > 1) {
            $item->decrement('quantity');
        } else {
            $item->delete();
        }
        $this->dispatch('cart-updated');
    }

    public function removeItem($itemId)
    {
        CartItem::where('id', $itemId)->delete();
        $this->dispatch('cart-updated');
    }
};
?>

<div class="space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Shopping Cart</h1>
        <a href="/catalog" class="text-sm font-bold text-indigo-600 hover:text-indigo-500 transition duration-200">
            &larr; Back to Catalog
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Items List -->
        <div class="lg:col-span-2 space-y-4">
            @forelse($this->items as $item)
                <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-slate-100 shadow-sm group hover:border-indigo-100 transition duration-300">
                    <div class="flex items-center space-x-6">
                        <div class="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900 leading-tight">{{ $item->name }}</h3>
                            <p class="text-sm text-slate-500 font-medium mt-1">
                                ${{ number_format($item->price, 2) }} per unit
                            </p>
                            <button wire:click="removeItem('{{ $item->id }}')" class="mt-2 text-xs font-bold text-red-500 hover:text-red-600 transition duration-150 uppercase tracking-widest">
                                Remove Item
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6">
                        <div class="flex items-center bg-slate-50 rounded-2xl p-1 border border-slate-100">
                            <button wire:click="decrement('{{ $item->id }}')" class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-indigo-600 transition duration-150 active:scale-90">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                </svg>
                            </button>
                            <span class="w-10 h-10 flex items-center justify-center text-lg font-black text-slate-900">{{ $item->quantity }}</span>
                            <button wire:click="increment('{{ $item->id }}')" class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-indigo-600 transition duration-150 active:scale-90">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                        </div>
                        <div class="text-right min-w-[100px]">
                            <p class="text-xl font-black text-slate-900">${{ number_format($item->price * $item->quantity, 2) }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-16 rounded-3xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-slate-300">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-extrabold text-slate-900">Your cart is empty</h3>
                    <p class="text-slate-500 mt-2 text-lg">Looks like you haven't added any services yet.</p>
                    <a href="/catalog" class="mt-8 px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition duration-200 transform active:scale-[0.98]">
                        Start Shopping
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Summary -->
        <div class="space-y-6">
            <div class="bg-slate-900 p-8 rounded-3xl text-white shadow-2xl shadow-slate-200 sticky top-24">
                <h2 class="text-xl font-bold mb-8 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-indigo-400">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    Order Summary
                </h2>

                <div class="space-y-4 mb-8">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal</span>
                        <span class="text-white font-bold">${{ number_format($this->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Service Fee</span>
                        <span class="text-white font-bold">$0.00</span>
                    </div>
                    <div class="pt-4 border-t border-white/10 flex justify-between">
                        <span class="text-lg font-bold">Total</span>
                        <span class="text-2xl font-black text-indigo-400 leading-none">${{ number_format($this->total, 2) }}</span>
                    </div>
                </div>

                <a href="/checkout" 
                    @class([
                        'w-full py-4 inline-flex items-center justify-center font-black rounded-2xl transition duration-200 transform active:scale-[0.98]',
                        'bg-indigo-500 text-white hover:bg-indigo-400 shadow-xl shadow-indigo-500/20' => $this->items->count() > 0,
                        'bg-slate-800 text-slate-500 cursor-not-allowed' => $this->items->count() === 0
                    ])
                    @if($this->items->count() === 0) onclick="return false;" @endif
                >
                    Proceed to Checkout
                </a>

                <p class="mt-6 text-center text-xs text-slate-500 font-medium">
                    Taxes and discounts will be calculated at checkout.
                </p>
            </div>
        </div>
    </div>
</div>