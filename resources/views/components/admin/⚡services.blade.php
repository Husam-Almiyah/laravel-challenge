<?php

use Livewire\Component;
use App\Domains\Catalog\Models\Service;
use App\Domains\Catalog\Models\Category;
use Illuminate\Support\Str;

new class extends Component
{
    public $services;
    public $categories;
    public $editingServiceId = null;
    
    // Form fields
    public $name;
    public $slug;
    public $description;
    public $price = 0;
    public $category_id;
    public $is_active = true;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->services = Service::with('category')->latest()->get();
        $this->categories = Category::all();
    }

    public function toggleActive($id)
    {
        $service = Service::find($id);
        $service->is_active = !$service->is_active;
        $service->save();
        $this->loadData();
    }

    public function create()
    {
        $this->resetForm();
        $this->editingServiceId = 'new';
        if ($this->categories->isNotEmpty()) {
            $this->category_id = $this->categories->first()->id;
        }
    }

    public function edit($id)
    {
        $service = Service::find($id);
        $this->editingServiceId = $id;
        $this->name = $service->name;
        $this->slug = $service->slug;
        $this->description = $service->description;
        $this->price = $service->price;
        $this->category_id = $service->category_id;
        $this->is_active = $service->is_active;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'slug' => 'required|alpha_dash|unique:services,slug,' . ($this->editingServiceId === 'new' ? 'NULL' : $this->editingServiceId) . ',id',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($this->editingServiceId === 'new') {
            Service::create([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'price' => $this->price,
                'is_active' => $this->is_active,
            ]);
        } else {
            $service = Service::find($this->editingServiceId);
            $service->update([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'price' => $this->price,
                'is_active' => $this->is_active,
            ]);
        }

        $this->cancelEdit();
        $this->loadData();
        session()->flash('message', 'Service saved successfully.');
    }

    public function delete($id)
    {
        Service::destroy($id);
        $this->loadData();
        session()->flash('message', 'Service deleted.');
    }

    public function cancelEdit()
    {
        $this->editingServiceId = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->price = 0;
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
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Maintenance Services</h2>
        <button wire:click="create" class="px-6 py-2 bg-indigo-600 text-white text-sm font-black rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
            Add Service
        </button>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Service</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Category</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Price</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($services as $service)
                    <tr class="hover:bg-slate-50/50 transition duration-150 group">
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900">{{ $service->name }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ $service->slug }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-2 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-lg uppercase tracking-tight">{{ $service->category?->name ?? 'Uncategorized' }}</span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-sm font-black text-slate-900">${{ number_format($service->price, 2) }}</span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <button wire:click="toggleActive('{{ $service->id }}')" 
                                @class([
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2',
                                    'bg-indigo-600' => $service->is_active,
                                    'bg-slate-200' => !$service->is_active,
                                ])>
                                <span class="sr-only">Toggle Status</span>
                                <span @class([
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    'translate-x-5' => $service->is_active,
                                    'translate-x-0' => !$service->is_active,
                                ])></span>
                            </button>
                        </td>
                        <td class="px-8 py-6 text-right space-x-3">
                            <button wire:click="edit('{{ $service->id }}')" class="text-xs font-black text-indigo-600 hover:text-indigo-500 uppercase tracking-widest">Edit</button>
                            <button wire:click="delete('{{ $service->id }}')" wire:confirm="Are you sure?" class="text-xs font-black text-rose-600 hover:text-rose-500 uppercase tracking-widest">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($editingServiceId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $editingServiceId === 'new' ? 'New Service' : 'Edit Service' }}</h3>
                    <button wire:click="cancelEdit" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-8 grid grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Service Name</label>
                            <input type="text" wire:model.blur="name" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold" placeholder="e.g. AC Maintenance">
                            @error('name') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Slug</label>
                            <input type="text" wire:model="slug" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold">
                            @error('slug') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Category</label>
                            <select wire:model="category_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Price ($)</label>
                            <input type="number" step="0.01" wire:model="price" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold">
                            @error('price') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Description</label>
                            <textarea wire:model="description" rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold" placeholder="Describe the service..."></textarea>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="w-5 h-5 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-black text-slate-900 uppercase tracking-widest">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-slate-50 flex items-center justify-end space-x-4">
                    <button wire:click="cancelEdit" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 transition">Cancel</button>
                    <button wire:click="save" class="px-10 py-3 bg-indigo-600 text-white text-sm font-black rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition active:scale-95">
                        Save Service
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
