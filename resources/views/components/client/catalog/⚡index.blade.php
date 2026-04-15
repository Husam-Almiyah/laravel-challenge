<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Domains\Catalog\Models\Service;
use App\Domains\Catalog\Models\Package;
use App\Domains\Booking\Models\Cart;
use App\Domains\Booking\Models\CartItem;

new #[Layout('layouts.app'), Title('Service Catalog - Ajeer Boost')] class extends Component
{
    public $services;
    public $packages;

    public function mount()
    {
        $this->services = Service::where('is_active', true)->with('category')->get();
        $this->packages = Package::where('is_active', true)->with('services')->get();
    }

    public function addToCart($id, $type)
    {
        if (!auth()->check()) {
            return $this->redirect('/login');
        }

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        
        $modelClass = $type === 'service' ? Service::class : Package::class;
        $itemable = $modelClass::findOrFail($id);

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('itemable_id', $id)
            ->where('itemable_type', $modelClass)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity');
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'itemable_id' => $itemable->id,
                'itemable_type' => $modelClass,
                'name' => $itemable->name,
                'price' => $itemable->price,
                'quantity' => 1,
            ]);
        }

        $this->dispatch('cart-updated');
    }
};
?>

<div class="space-y-12">
    <!-- Hero Section -->
    <div class="relative rounded-3xl overflow-hidden bg-slate-900 py-24 px-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-violet-600/20 mix-blend-overlay"></div>
        <div class="relative max-w-2xl">
            <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight">Our Maintenance Services</h1>
            <p class="mt-6 text-indigo-100 text-lg md:text-xl leading-relaxed">
                From simple repairs to comprehensive maintenance packages, we've got you covered. 
                Select the services that fit your needs and schedule them at your convenience.
            </p>
        </div>
    </div>

    <!-- Multi-Service Packages Section -->
    <section class="space-y-8">
        <div class="flex items-center space-x-3">
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Service Packages</h2>
            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs font-black rounded-full tracking-widest uppercase">Best Value</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($packages as $package)
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col overflow-hidden group">
                    <div class="p-8 flex-grow">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-2xl font-bold text-slate-900 leading-none">{{ $package->name }}</h3>
                            <div class="text-indigo-600 font-black text-xl">${{ number_format($package->price, 2) }}</div>
                        </div>
                        
                        <p class="text-slate-500 mb-8 line-clamp-3 leading-relaxed">{{ $package->description }}</p>

                        <div class="space-y-3 mb-8">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Included Services</h4>
                            @foreach($package->services as $pService)
                                <div class="flex items-center text-sm text-slate-600">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    {{ $pService->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-8 pt-0 mt-auto">
                        <button wire:click="addToCart('{{ $package->id }}', 'package')"
                            class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition duration-200 active:scale-[0.98]">
                            Add Package to Cart
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <hr class="border-slate-100">

    <!-- Individual Services Section -->
    <section class="space-y-8">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Individual Services</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($services as $service)
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:border-indigo-200 transition duration-300">
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] font-black tracking-widest text-indigo-500 uppercase">{{ $service->category->name ?? 'Service' }}</span>
                                <h3 class="text-lg font-bold text-slate-900 mt-1">{{ $service->name }}</h3>
                            </div>
                        </div>
                        
                        <p class="text-slate-500 text-sm line-clamp-2 leading-relaxed">{{ $service->description }}</p>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                            <span class="text-xl font-black text-slate-900">${{ number_format($service->price, 2) }}</span>
                            <button wire:click="addToCart('{{ $service->id }}', 'service')" 
                                class="p-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>