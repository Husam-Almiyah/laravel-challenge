<?php

use Livewire\Component;
use App\Domains\Payments\Models\PaymentGateway;
use App\Models\City;
use App\Domains\Catalog\Enums\ModuleEnum;
use App\Domains\Account\Enums\UserStatus;
use Illuminate\Support\Str;

new class extends Component
{
    public $gateways;
    public $cities;
    public $editingGatewayId = null;
    public $selectedCities = [];
    public $selectedModules = [];
    public $minAmount = 0;
    public $requiredStatus = '';
    public $selectedDays = [];

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
        $this->selectedModules = $gateway->rules['modules'] ?? [];
        $this->minAmount = $gateway->rules['min_amount'] ?? 0;
        $this->requiredStatus = $gateway->rules['required_status'] ?? '';
        $this->selectedDays = $gateway->rules['allowed_days'] ?? [];
    }

    public function saveRules()
    {
        $gateway = PaymentGateway::find($this->editingGatewayId);
        
        $rules = [];
        
        // Cities (strictly as integers)
        if (!empty($this->selectedCities)) {
            $rules['cities'] = array_map('intval', $this->selectedCities);
        }
        
        // Modules
        if (!empty($this->selectedModules)) {
            $rules['modules'] = $this->selectedModules;
        }
        
        // Min Amount
        if ($this->minAmount > 0) {
            $rules['min_amount'] = (float) $this->minAmount;
        }
        
        // Required Status
        if (!empty($this->requiredStatus)) {
            $rules['required_status'] = $this->requiredStatus;
        }

        // Allowed Days (integers 0-6)
        if (!empty($this->selectedDays)) {
            $rules['allowed_days'] = array_map('intval', $this->selectedDays);
        }
        
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
                                @php 
                                    $rules = $gateway->rules ?? [];
                                    $cityCount = count($rules['cities'] ?? []);
                                    $modules = $rules['modules'] ?? [];
                                    $minAmount = $rules['min_amount'] ?? 0;
                                    $status = $rules['required_status'] ?? null;
                                    $daysCount = count($rules['allowed_days'] ?? []);
                                @endphp

                                @if($cityCount > 0)
                                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg uppercase tracking-wider">
                                        {{ $cityCount }} Cities
                                    </span>
                                @endif

                                @if($daysCount > 0)
                                    <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-1 rounded-lg uppercase tracking-wider">
                                        {{ $daysCount }} Days
                                    </span>
                                @endif

                                @foreach($modules as $module)
                                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg uppercase tracking-wider">
                                        {{ $module }}
                                    </span>
                                @endforeach

                                @if($status)
                                    <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2 py-1 rounded-lg uppercase tracking-wider">
                                        {{ $status }} Only
                                    </span>
                                @endif

                                @if($minAmount > 0)
                                    <span class="text-[10px] font-black text-slate-600 bg-slate-100 px-2 py-1 rounded-lg uppercase tracking-wider">
                                        Min: ${{ number_format($minAmount, 2) }}
                                    </span>
                                @endif

                                @if(empty($rules))
                                    <span class="text-[10px] font-black text-slate-400 bg-slate-50 px-2 py-1 rounded-lg uppercase tracking-wider">No Restrictions</span>
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
                    <p class="text-sm text-slate-500 mt-1">Configure availability rules for this payment method.</p>
                </div>
                
                <div class="p-8 space-y-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <!-- City Restrictions -->
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">City Restrictions</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($cities as $city)
                                @php $isSelected = in_array((int)$city->id, array_map('intval', $this->selectedCities)) @endphp
                                <label class="group flex items-center p-3 rounded-2xl border-2 transition-all cursor-pointer @if($isSelected) border-indigo-600 bg-indigo-50 @else border-slate-100 hover:border-slate-200 @endif">
                                    <input type="checkbox" value="{{ $city->id }}" wire:model.live="selectedCities" class="hidden">
                                    <div @class([
                                        'w-4 h-4 rounded-full border-2 mr-3 transition-all',
                                        'border-indigo-600 bg-indigo-600' => $isSelected,
                                        'border-slate-200 bg-white' => !$isSelected,
                                    ])></div>
                                    <span @class([
                                        'text-sm font-bold',
                                        'text-indigo-700' => $isSelected,
                                        'text-slate-600' => !$isSelected,
                                    ])>{{ $city->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Module Availability -->
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Module Availability</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach(ModuleEnum::cases() as $module)
                                @php $isModSelected = in_array($module->value, $this->selectedModules) @endphp
                                <label class="flex items-center p-3 px-5 rounded-2xl border-2 transition-all cursor-pointer @if($isModSelected) border-emerald-600 bg-emerald-50 @else border-slate-100 hover:border-slate-200 @endif">
                                    <input type="checkbox" value="{{ $module->value }}" wire:model.live="selectedModules" class="hidden">
                                    <span @class([
                                        'text-sm font-bold uppercase tracking-wide',
                                        'text-emerald-700' => $isModSelected,
                                        'text-slate-600' => !$isModSelected,
                                    ])>{{ $module->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <!-- User Status -->
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Required User Status</label>
                            <select wire:model.live="requiredStatus" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-600 transition">
                                <option value="">Any Status</option>
                                @foreach([UserStatus::GUEST, UserStatus::VERIFIED] as $status)
                                    <option value="{{ $status->value }}">{{ Str::title($status->value) }} Only</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Minimum Amount -->
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Minimum Amount (SAR)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input type="number" step="0.01" wire:model.live="minAmount" class="w-full bg-slate-50 border-none rounded-2xl p-4 pl-8 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-600 transition" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Allowed Days -->
                    <div class="pt-6 border-t border-slate-100">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Allowed Days (Week Availability)</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $index => $day)
                                @php $isDaySelected = in_array($index, array_map('intval', $this->selectedDays)) @endphp
                                <label class="flex-1 min-w-[80px] flex items-center justify-center p-3 rounded-2xl border-2 transition-all cursor-pointer @if($isDaySelected) border-rose-600 bg-rose-50 @else border-slate-100 hover:border-slate-200 @endif">
                                    <input type="checkbox" value="{{ $index }}" wire:model.live="selectedDays" class="hidden">
                                    <span @class([
                                        'text-xs font-black uppercase tracking-widest',
                                        'text-rose-700' => $isDaySelected,
                                        'text-slate-400' => !$isDaySelected,
                                    ])>{{ $day }}</span>
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