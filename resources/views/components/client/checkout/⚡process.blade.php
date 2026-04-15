<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\City;
use App\Domains\Booking\Models\Cart;
use App\Domains\Payments\Services\GatewayAvailabilityResolver;
use App\Domains\Payments\Models\PaymentGateway;

new #[Layout('layouts.app'), Title('Secure Checkout - Ajeer Boost')] class extends Component
{
    public int $step = 1;
    
    // Step 1: Address
    public $cities;
    public string $selectedCityId = '';
    public string $addressDetails = '';

    // Step 2: Payment
    public $availableGateways = [];
    public string $selectedGatewayId = '';

    public function mount()
    {
        $this->cities = City::all();
        $user = auth()->user();
        $defaultAddress = $user->addresses()->where('is_default', true)->first();
        
        if ($defaultAddress) {
            $this->selectedCityId = $defaultAddress->city_id;
            $this->addressDetails = $defaultAddress->address_details;
        }

        if ($user->cart?->items->count() === 0) {
            return $this->redirect('/catalog');
        }
    }

    public function goToPayment()
    {
        $this->validate([
            'selectedCityId' => 'required',
            'addressDetails' => 'required|min:5',
        ]);

        $this->resolveGateways();
        $this->step = 2;
    }

    public function resolveGateways()
    {
        $resolver = app(GatewayAvailabilityResolver::class);
        $user = auth()->user();
        $cart = $user->cart;
        $total = $cart->items->sum(fn($item) => $item->price * $item->quantity);

        $context = [
            'user' => $user,
            'city_id' => $this->selectedCityId,
            'amount' => $total,
            'module' => 'maintenance', // Example module
        ];

        $this->availableGateways = $resolver->getAvailableGateways($context);
        
        if ($this->availableGateways->isNotEmpty()) {
            $this->selectedGatewayId = $this->availableGateways->first()->id;
        }
    }

    public function completeCheckout()
    {
        $this->validate([
            'selectedGatewayId' => 'required',
        ]);

        // Simulate payment processing
        sleep(1);

        // Clear cart
        auth()->user()->cart->items()->delete();

        $this->step = 3;
    }
};
?>

<div class="max-w-4xl mx-auto py-12 px-4 shadow-sm border border-slate-100 rounded-3xl bg-white mb-10">
    <!-- Stepper -->
    <div class="relative flex items-center justify-between mb-16">
        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
        <div class="absolute top-1/2 left-0 h-0.5 bg-indigo-600 -translate-y-1/2 z-0 transition-all duration-500" 
             style="width: {{ ($step - 1) * 50 }}%"></div>
        
        @foreach(['Address', 'Payment', 'Confirmation'] as $i => $label)
            <div class="relative z-10 flex flex-col items-center">
                <div @class([
                    'w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-300',
                    'bg-indigo-600 text-white shadow-lg shadow-indigo-200' => $step >= ($i + 1),
                    'bg-white text-slate-400 border-2 border-slate-100' => $step < ($i + 1),
                ])>
                    {{ $i + 1 }}
                </div>
                <span @class([
                    'mt-2 text-xs font-black uppercase tracking-widest',
                    'text-indigo-600' => $step >= ($i + 1),
                    'text-slate-400' => $step < ($i + 1),
                ])>{{ $label }}</span>
            </div>
        @endforeach
    </div>

    @if($step === 1)
        <!-- Step 1: Address -->
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="text-center">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Where should we visit?</h2>
                <p class="text-slate-500 mt-2">Provide your address for the maintenance service.</p>
            </div>

            <div class="space-y-6 max-w-lg mx-auto">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Select City</label>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($cities as $city)
                            <button wire:click="$set('selectedCityId', '{{ $city->id }}')"
                                @class([
                                    'py-4 px-6 rounded-2xl border-2 transition-all duration-200 text-sm font-bold',
                                    'border-indigo-600 bg-indigo-50 text-indigo-700' => $selectedCityId == $city->id,
                                    'border-slate-100 bg-white text-slate-500 hover:border-indigo-200' => $selectedCityId != $city->id,
                                ])>
                                {{ $city->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Street & Building Details</label>
                    <textarea wire:model="addressDetails" rows="3" placeholder="Enter your full street address..."
                        class="w-full px-4 py-3 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:border-indigo-600 focus:ring-0 transition-all duration-200 placeholder:text-slate-400"></textarea>
                    @error('addressDetails') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                </div>

                <button wire:click="goToPayment"
                    class="w-full py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all duration-200 transform active:scale-[0.98]">
                    Continue to Payment
                </button>
            </div>
        </div>
    @elseif($step === 2)
        <!-- Step 2: Payment -->
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="text-center">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Choose Payment Method</h2>
                <p class="text-slate-500 mt-2">Methods filtered based on your city: <span class="text-indigo-600 font-bold">{{ $cities->find($selectedCityId)->name }}</span></p>
            </div>

            <div class="space-y-6 max-w-lg mx-auto">
                <div class="space-y-4">
                    @forelse($availableGateways as $gateway)
                        <button wire:click="$set('selectedGatewayId', '{{ $gateway->id }}')"
                            @class([
                                'w-full p-6 rounded-2xl border-2 flex items-center justify-between transition-all duration-200 group',
                                'border-indigo-600 bg-indigo-50 text-indigo-700 shadow-md' => $selectedGatewayId == $gateway->id,
                                'border-slate-100 bg-white text-slate-500 hover:border-indigo-200' => $selectedGatewayId != $gateway->id,
                            ])>
                            <div class="flex items-center space-x-4">
                                <div @class([
                                    'w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200',
                                    'bg-indigo-600 text-white' => $selectedGatewayId == $gateway->id,
                                    'bg-slate-100 text-slate-500 group-hover:bg-indigo-50 group-hover:text-indigo-600' => $selectedGatewayId != $gateway->id,
                                ])>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.5V15h-3m0 0v-4.5m0 4.5H9M3 12h12M3 13.5v-1.5M3 10.5v1.5m0-1.5V12m0 0h12m0 0V9m0-1.5H3m12 1.5V7.5M3 7.5h12" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <span class="block text-lg font-black tracking-tight">{{ $gateway->name }}</span>
                                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">Secure Transaction</span>
                                </div>
                            </div>
                            @if($selectedGatewayId == $gateway->id)
                                <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            @endif
                        </button>
                    @empty
                        <div class="p-8 bg-red-50 rounded-2xl border-2 border-red-100 text-center">
                            <p class="text-red-700 font-bold">No payment methods available for your city.</p>
                            <button wire:click="$set('step', 1)" class="mt-4 text-xs font-black uppercase tracking-widest text-red-700 hover:text-red-900 transition underline underline-offset-4 decoration-2">Change Address</button>
                        </div>
                    @endforelse
                </div>

                @if(!empty($availableGateways))
                    <button wire:click="completeCheckout" wire:loading.attr="disabled"
                        class="w-full py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all duration-200 transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-wait">
                        <span wire:loading.remove>Finalize Checkout</span>
                        <span wire:loading>Processing Payment...</span>
                    </button>
                    <button wire:click="$set('step', 1)" class="w-full text-center text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">Go Back</button>
                @endif
            </div>
        </div>
    @elseif($step === 3)
        <!-- Step 3: Confirmation -->
        <div class="py-12 text-center animate-in zoom-in duration-500 space-y-8">
            <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-12 h-12">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Order Confirmed!</h2>
                <p class="text-slate-500 mt-4 text-lg">Thank you for choosing Ajeer Boost. Your maintenance services have been scheduled.</p>
            </div>

            <div class="pt-8">
                <a href="/dashboard" class="inline-flex items-center px-8 py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition-all duration-200 shadow-xl shadow-slate-200">
                    Go to Dashboard
                </a>
            </div>
        </div>
    @endif
</div>