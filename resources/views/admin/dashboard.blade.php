<x-app-layout>
    <div class="space-y-10">
        <!-- Admin Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">System Control Center</h1>
                <p class="mt-2 text-slate-500 text-lg font-medium">Configure payment gateways, monitor transactions, and manage the service catalog.</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 text-sm font-black rounded-xl border border-indigo-100 uppercase tracking-widest">
                    <span class="w-2 h-2 bg-indigo-600 rounded-full mr-2 animate-pulse"></span>
                    Admin Mode
                </span>
            </div>
        </div>

        <!-- System Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Total Volume</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2">$12,450.00</h3>
                <p class="text-xs text-green-600 font-bold mt-2">+12.5% from last month</p>
            </div>
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Active Trials</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2">156</h3>
                <p class="text-xs text-indigo-600 font-bold mt-2">Currently being monitored</p>
            </div>
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Active Gateways</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2">{{ \App\Domains\Payments\Models\PaymentGateway::where('is_active', true)->count() }}</h3>
                <p class="text-xs text-amber-600 font-bold mt-2">Optimized for conversion</p>
            </div>
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Sys Health</p>
                <h3 class="text-3xl font-black text-green-500 mt-2">99.9%</h3>
                <p class="text-xs text-slate-400 font-bold mt-2">All services operational</p>
            </div>
        </div>

        <!-- Main Admin Content -->
        <livewire:admin.dashboard />
    </div>
</x-app-layout>
