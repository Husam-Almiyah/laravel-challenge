<?php

use Livewire\Component;
use App\Domains\Catalog\Models\Category;
use Illuminate\Support\Str;

new class extends Component
{
    public $categories;
    public $editingCategoryId = null;
    
    // Form fields
    public $name;
    public $slug;
    public $priority = 0;
    public $is_active = true;

    public function mount()
    {
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = Category::orderBy('priority')->get();
    }

    public function toggleActive($id)
    {
        $category = Category::find($id);
        $category->is_active = !$category->is_active;
        $category->save();
        $this->loadCategories();
    }

    public function create()
    {
        $this->resetForm();
        $this->editingCategoryId = 'new';
    }

    public function edit($id)
    {
        $category = Category::find($id);
        $this->editingCategoryId = $id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->priority = $category->priority;
        $this->is_active = $category->is_active;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'slug' => 'required|alpha_dash|unique:categories,slug,' . ($this->editingCategoryId === 'new' ? 'NULL' : $this->editingCategoryId) . ',id',
            'priority' => 'integer',
        ]);

        if ($this->editingCategoryId === 'new') {
            Category::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'priority' => $this->priority,
                'is_active' => $this->is_active,
            ]);
        } else {
            $category = Category::find($this->editingCategoryId);
            $category->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'priority' => $this->priority,
                'is_active' => $this->is_active,
            ]);
        }

        $this->cancelEdit();
        $this->loadCategories();
        session()->flash('message', 'Category saved successfully.');
    }

    public function delete($id)
    {
        Category::destroy($id);
        $this->loadCategories();
        session()->flash('message', 'Category deleted.');
    }

    public function cancelEdit()
    {
        $this->editingCategoryId = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->slug = '';
        $this->priority = 0;
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
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">System Categories</h2>
        <button wire:click="create" class="px-6 py-2 bg-indigo-600 text-white text-sm font-black rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
            Add Category
        </button>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Name</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Slug</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Priority</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($categories as $category)
                    <tr class="hover:bg-slate-50/50 transition duration-150 group">
                        <td class="px-8 py-6">
                            <span class="font-bold text-slate-900">{{ $category->name }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $category->slug }}</span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-black rounded-lg">{{ $category->priority }}</span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <button wire:click="toggleActive('{{ $category->id }}')" 
                                @class([
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2',
                                    'bg-indigo-600' => $category->is_active,
                                    'bg-slate-200' => !$category->is_active,
                                ])>
                                <span class="sr-only">Toggle Status</span>
                                <span @class([
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    'translate-x-5' => $category->is_active,
                                    'translate-x-0' => !$category->is_active,
                                ])></span>
                            </button>
                        </td>
                        <td class="px-8 py-6 text-right space-x-3">
                            <button wire:click="edit('{{ $category->id }}')" class="text-xs font-black text-indigo-600 hover:text-indigo-500 uppercase tracking-widest">Edit</button>
                            <button wire:click="delete('{{ $category->id }}')" wire:confirm="Are you sure?" class="text-xs font-black text-rose-600 hover:text-rose-500 uppercase tracking-widest">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($editingCategoryId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-slate-100">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $editingCategoryId === 'new' ? 'New Category' : 'Edit Category' }}</h3>
                </div>
                
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Category Name</label>
                        <input type="text" wire:model.blur="name" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold" placeholder="e.g. Plumbing">
                        @error('name') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Slug</label>
                        <input type="text" wire:model="slug" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold" placeholder="plumbing-services">
                        @error('slug') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Priority</label>
                            <input type="number" wire:model="priority" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-bold">
                        </div>
                        <div class="flex items-center pt-8">
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
                        Save Category
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
