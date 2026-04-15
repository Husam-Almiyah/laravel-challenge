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
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
            <!-- Gateway Management (Livewire) -->
            <div class="xl:col-span-2 space-y-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Payment Gateways</h2>
                    <button class="text-sm font-bold text-indigo-600 hover:text-indigo-500 transition-colors uppercase tracking-widest">Detailed Logs</button>
                </div>
                <livewire:admin.gateways />
            </div>

            <!-- Management Tools Sidebar -->
            <div class="space-y-8">
                <div class="bg-slate-900 p-8 rounded-3xl text-white shadow-2xl shadow-slate-200">
                    <h2 class="text-xl font-bold mb-6 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-indigo-400">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0m-9.75 0h9.75" />
                        </svg>
                        Catalog Control
                    </h2>
                    <div class="space-y-4">
                        <button class="w-full flex items-center justify-between p-4 bg-white/10 border border-white/5 rounded-2xl hover:bg-white/20 transition group">
                            <span class="font-bold text-sm">Services Management</span>
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                        <button class="w-full flex items-center justify-between p-4 bg-white/10 border border-white/5 rounded-2xl hover:bg-white/20 transition group">
                            <span class="font-bold text-sm">Package Configurator</span>
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                        <button class="w-full flex items-center justify-between p-4 bg-white/10 border border-white/5 rounded-2xl hover:bg-white/20 transition group">
                            <span class="font-bold text-sm">Category Schema</span>
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                    <h2 class="font-black text-slate-900 mb-6 tracking-tight">Live Activity</h2>
                    <div class="space-y-6">
                        @foreach(range(1, 3) as $i)
                            <div class="flex items-start space-x-4">
                                <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 leading-none">Order #{{ 450 + $i }} confirmed</p>
                                    <p class="text-xs text-slate-400 font-bold mt-1 uppercase tracking-widest">$249.00 • 2 mins ago</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
