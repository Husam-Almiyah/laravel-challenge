<?php

use Livewire\Component;

new class extends Component
{
    public $currentTab = 'gateways'; // gateways, services, packages, categories

    public function setTab($tab)
    {
        $this->currentTab = $tab;
    }
};
?>

<div class="grid grid-cols-1 xl:grid-cols-4 gap-10">
    <!-- Sidebar -->
    <div class="space-y-8">
        <div class="bg-indigo-950 p-8 rounded-[2.5rem] text-white shadow-2xl shadow-indigo-200/50">
            <h2 class="text-xl font-bold mb-8 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 mr-3 text-indigo-400">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0m-9.75 0h9.75" />
                </svg>
                Control Panel
            </h2>
            <div class="space-y-3">
                <button wire:click="setTab('gateways')" @class([
                    'w-full flex items-center justify-between p-4 rounded-2xl transition-all group',
                    'bg-indigo-600 shadow-lg shadow-indigo-900/50' => $currentTab === 'gateways',
                    'hover:bg-white/10' => $currentTab !== 'gateways'
                ])>
                    <span class="font-bold text-sm">Payment Gateways</span>
                    <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <div class="pt-4 pb-2">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest px-4 mb-3">Catalog Management</p>
                    <div class="space-y-2">
                        <button wire:click="setTab('services')" @class([
                            'w-full flex items-center justify-between p-4 rounded-2xl transition-all group',
                            'bg-indigo-600 shadow-lg shadow-indigo-900/50' => $currentTab === 'services',
                            'hover:bg-white/10' => $currentTab !== 'services'
                        ])>
                            <span class="font-bold text-sm">Services</span>
                            <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                        <button wire:click="setTab('packages')" @class([
                            'w-full flex items-center justify-between p-4 rounded-2xl transition-all group',
                            'bg-indigo-600 shadow-lg shadow-indigo-900/50' => $currentTab === 'packages',
                            'hover:bg-white/10' => $currentTab !== 'packages'
                        ])>
                            <span class="font-bold text-sm">Packages</span>
                            <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                        <button wire:click="setTab('categories')" @class([
                            'w-full flex items-center justify-between p-4 rounded-2xl transition-all group',
                            'bg-indigo-600 shadow-lg shadow-indigo-900/50' => $currentTab === 'categories',
                            'hover:bg-white/10' => $currentTab !== 'categories'
                        ])>
                            <span class="font-bold text-sm">Categories</span>
                            <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Summary -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <h2 class="font-black text-slate-900 mb-6 tracking-tight flex items-center">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                System Health
            </h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Uptime</span>
                    <span class="text-xs font-black text-slate-900">99.98%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Latency</span>
                    <span class="text-xs font-black text-slate-900">12ms</span>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-50">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">All services are currently operational. No incidents reported.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="xl:col-span-3 space-y-6">
        <div class="min-h-[600px] animate-in fade-in slide-in-from-bottom-2 duration-500">
            @if($currentTab === 'gateways')
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Payment Gateways</h2>
                    <p class="text-slate-500 font-medium mt-1">Configure your transaction processors and availability rules.</p>
                </div>
                <livewire:admin.gateways />
            @elseif($currentTab === 'services')
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Services</h2>
                    <p class="text-slate-500 font-medium mt-1">Manage the core maintenance offerings in your catalog.</p>
                </div>
                <livewire:admin.services />
            @elseif($currentTab === 'packages')
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Packages</h2>
                    <p class="text-slate-500 font-medium mt-1">Create bundles of multiple services at special rates.</p>
                </div>
                <livewire:admin.packages />
            @elseif($currentTab === 'categories')
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Categories</h2>
                    <p class="text-slate-500 font-medium mt-1">Organize your services into logical groups for browsing.</p>
                </div>
                <livewire:admin.categories />
            @endif
        </div>
    </div>
</div>
