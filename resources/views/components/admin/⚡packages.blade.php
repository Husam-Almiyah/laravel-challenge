<?php

use Livewire\Component;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use Illuminate\Support\Str;

new class extends Component
{
    public $packages;
    public $servicesList;
    public $editingPackageId = null;
    
    // Form fields
    public $name;
    public $slug;
    public $description;
    public $price = 0;
    public $discount_percentage = 0;
    public $selectedServices = [];
    public $is_active = true;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->packages = Package::with('services')->latest()->get();
        $this->servicesList = Service::where('is_active', true)->get();
    }

    public function toggleActive($id)
    {
        $package = Package::find($id);
        $package->is_active = !$package->is_active;
        $package->save();
        $this->loadData();
    }

    public function create()
    {
        $this->resetForm();
        $this->editingPackageId = 'new';
    }

    public function edit($id)
    {
        $package = Package::find($id);
        $this->editingPackageId = $id;
        $this->name = $package->name;
        $this->slug = $package->slug;
        $this->description = $package->description;
        $this->price = $package->price;
        $this->discount_percentage = $package->discount_percentage;
        $this->selectedServices = $package->services->pluck('id')->map('strval')->toArray();
        $this->is_active = $package->is_active;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'slug' => 'required|alpha_dash|unique:packages,slug,' . ($this->editingPackageId === 'new' ? 'NULL' : $this->editingPackageId) . ',id',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'numeric|min:0|max:100',
            'selectedServices' => 'required|array|min:1',
        ]);

        if ($this->editingPackageId === 'new') {
            $package = Package::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'price' => $this->price,
                'discount_percentage' => $this->discount_percentage,
                'is_active' => $this->is_active,
            ]);
        } else {
            $package = Package::find($this->editingPackageId);
            $package->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'price' => $this->price,
                'discount_percentage' => $this->discount_percentage,
                'is_active' => $this->is_active,
            ]);
        }

        $package->services()->sync($this->selectedServices);

        $this->cancelEdit();
        $this->loadData();
        session()->flash('message', 'Package saved successfully.');
    }

    public function delete($id)
    {
        Package::destroy($id);
        $this->loadData();
        session()->flash('message', 'Package deleted.');
    }

    public function cancelEdit()
    {
        $this->editingPackageId = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->price = 0;
        $this->discount_percentage = 0;
        $this->selectedServices = [];
        $this->is_active = true;
    }

    public function updatedName($value)
    {
        if (!$this->slug) {
            $this->slug = Str::slug($value);
        }
    }
};
?>

<div class="space-y-6">
    @if (session()->has('message'))
        <div class="p-4 bg-green-50 border border-green-100 text-green-700 rounded-2xl font-bold text-sm animate-in fade-in duration-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Service Packages</h2>
        <button wire:click="create" class="px-6 py-2 bg-indigo-600 text-white text-sm font-black rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
            Create Package
        </button>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Package</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Included Services</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Value</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($packages as $package)
                    <tr class="hover:bg-slate-50/50 transition duration-150 group">
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900">{{ $package->name }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ $package->discount_percentage }}% Discount Bundle</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-wrap gap-1 max-w-sm">
                                @foreach($package->services as $service)
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-black rounded uppercase tracking-tight">{{ $service->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-sm font-black text-slate-900">${{ number_format($package->price, 2) }}</span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <button wire:click="toggleActive('{{ $package->id }}')" 
                                @class([
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2',
                                    'bg-indigo-600' => $package->is_active,
                                    'bg-slate-200' => !$package->is_active,
                                ])>
                                <span class="sr-only">Toggle Status</span>
                                <span @class([
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    'translate-x-5' => $package->is_active,
                                    'translate-x-0' => !$package->is_active,
                                ])></span>
                            </button>
                        </td>
                        <td class="px-8 py-6 text-right space-x-3">
                            <button wire:click="edit('{{ $package->id }}')" class="text-xs font-black text-indigo-600 hover:text-indigo-500 uppercase tracking-widest">Edit</button>
                            <button wire:click="delete('{{ $package->id }}')" wire:confirm="Are you sure?" class="text-xs font-black text-rose-600 hover:text-rose-500 uppercase tracking-widest">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($editingPackageId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $editingPackageId === 'new' ? 'New Package' : 'Edit Package' }}</h3>
                    <button wire:click="cancelEdit" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-8 grid grid-cols-5 gap-10">
                    <div class="col-span-2 space-y-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Package Name</label>
                            <input type="text" wire:model.blur="name" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold" placeholder="e.g. Home Essentials">
                            @error('name') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Slug</label>
                            <input type="text" wire:model="slug" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold">
                            @error('slug') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Price ($)</label>
                                <input type="number" step="0.01" wire:model="price" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold">
                                @error('price') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Discount %</label>
                                <input type="number" wire:model="discount_percentage" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold">
                                @error('discount_percentage') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Description</label>
                            <textarea wire:model="description" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold" placeholder="Describe the package..."></textarea>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="w-5 h-5 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-black text-slate-900 uppercase tracking-widest">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-span-3 space-y-4">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Select Included Services</label>
                        <div class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                            <div class="max-h-[400px] overflow-y-auto divide-y divide-slate-100">
                                @foreach($servicesList as $service)
                                    @php $isSelected = in_array((string)$service->id, $selectedServices) @endphp
                                    <label class="flex items-center justify-between p-4 hover:bg-white transition cursor-pointer group @if($isSelected) bg-indigo-50/50 @endif">
                                        <div class="flex items-center space-x-3">
                                            <input type="checkbox" value="{{ $service->id }}" wire:model.live="selectedServices" class="w-5 h-5 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-900">{{ $service->name }}</span>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $service->category?->name ?? 'Service' }}</span>
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-slate-400 group-hover:text-indigo-600 transition">${{ number_format($service->price, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('selectedServices') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="p-8 bg-slate-50 flex items-center justify-end space-x-4">
                    <button wire:click="cancelEdit" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 transition">Cancel</button>
                    <button wire:click="save" class="px-10 py-3 bg-indigo-600 text-white text-sm font-black rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition active:scale-95">
                        Save Package
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
