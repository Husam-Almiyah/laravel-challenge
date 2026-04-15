<?php

use Livewire\Component;
use App\Domains\Payments\Models\PaymentGateway;
use App\Models\City;

new class extends Component
{
    public $gateways;
    public $cities;
    public $editingGatewayId = null;
    public $selectedCities = [];

    public function mount()
    {
        $this->loadGateways();
        $this->cities = City::all();
    }

    public function loadGateways()
    {
        $this->gateways = PaymentGateway::orderBy('priority')->get();
    }

    public function toggleActive($id)
    {
        $gateway = PaymentGateway::find($id);
        $gateway->is_active = !$gateway->is_active;
        $gateway->save();
        $this->loadGateways();
    }

    public function editRules($id)
    {
        $this->editingGatewayId = $id;
        $gateway = PaymentGateway::find($id);
        $this->selectedCities = $gateway->rules['cities'] ?? [];
    }

    public function saveRules()
    {
        $gateway = PaymentGateway::find($this->editingGatewayId);
        $rules = $gateway->rules ?? [];
        $rules['cities'] = array_map('strval', $this->selectedCities);
        
        $gateway->rules = $rules;
        $gateway->save();
        
        $this->editingGatewayId = null;
        $this->loadGateways();
        session()->flash('message', 'Rules updated successfully.');
    }

    public function cancelEdit()
    {
        $this->editingGatewayId = null;
    }
};
?>

<div class="space-y-6">
    @if (session()->has('message'))
        <div class="p-4 bg-green-50 border border-green-100 text-green-700 rounded-2xl font-bold text-sm animate-in fade-in duration-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Gateway</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Driver</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Rules</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($gateways as $gateway)
                    <tr class="hover:bg-slate-50/50 transition duration-150 group">
                        <td class="px-8 py-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 font-black">
                                    {{ substr($gateway->name, 0, 1) }}
                                </div>
                                <span class="font-bold text-slate-900">{{ $gateway->name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-2 py-1 bg-slate-100 text-slate-600 text-[10px] font-black rounded-lg uppercase tracking-tight">{{ $gateway->driver }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-wrap gap-2">
                                @php $cityCount = count($gateway->rules['cities'] ?? []) @endphp
                                @if($cityCount > 0)
                                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">
                                        {{ $cityCount }} Cities Restricted
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-lg">Available Everywhere</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <button wire:click="toggleActive('{{ $gateway->id }}')" 
                                @class([
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2',
                                    'bg-indigo-600' => $gateway->is_active,
                                    'bg-slate-200' => !$gateway->is_active,
                                ])>
                                <span class="sr-only">Toggle Status</span>
                                <span @class([
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    'translate-x-5' => $gateway->is_active,
                                    'translate-x-0' => !$gateway->is_active,
                                ])></span>
                            </button>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <button wire:click="editRules('{{ $gateway->id }}')" class="text-sm font-bold text-indigo-600 hover:text-indigo-500 transition-colors uppercase tracking-widest">
                                Manage Rules
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($editingGatewayId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-slate-100">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Manage Gateway Rules</h3>
                    <p class="text-sm text-slate-500 mt-1">Configure which cities can use this payment method.</p>
                </div>
                
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-4">City Restrictions</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($cities as $city)
                                <label class="flex items-center p-4 rounded-2xl border-2 transition-all cursor-pointer @if(in_array($city->id, $selectedCities)) border-indigo-600 bg-indigo-50 @else border-slate-100 hover:border-indigo-200 @endif">
                                    <input type="checkbox" value="{{ $city->id }}" wire:model="selectedCities" class="hidden">
                                    <span @class([
                                        'text-sm font-bold',
                                        'text-indigo-700' => in_array($city->id, $selectedCities),
                                        'text-slate-600' => !in_array($city->id, $selectedCities),
                                    ])>{{ $city->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-slate-50 flex items-center justify-end space-x-4">
                    <button wire:click="cancelEdit" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 transition">Cancel</button>
                    <button wire:click="saveRules" class="px-6 py-3 bg-indigo-600 text-white text-sm font-black rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition active:scale-95">
                        Save Rules
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>