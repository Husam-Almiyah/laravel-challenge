<x-app-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Client Dashboard</h1>
                <p class="mt-2 text-slate-500 text-lg">Manage your maintenance subscriptions and service bookings.</p>
            </div>
            <a href="/catalog" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition duration-200 transform active:scale-[0.98]">
                Browse Services
            </a>
        </div>

        <!-- Trial Info / Subscriptions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Active Subscriptions -->
            <div class="lg:col-span-2 space-y-6">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-indigo-600">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    My Subscriptions
                </h2>
                
                @forelse(auth()->user()->subscriptions as $subscription)
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-indigo-200 transition duration-300">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 bg-indigo-50 rounded-xl group-hover:bg-indigo-100 transition duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-indigo-600">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">{{ $subscription->plan->name }}</h3>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span @class([
                                        'px-2 py-0.5 text-xs font-bold rounded-full',
                                        'bg-green-100 text-green-700' => $subscription->status->value === 'active',
                                        'bg-slate-100 text-slate-700' => $subscription->status->value !== 'active',
                                    ])>
                                        {{ ucfirst($subscription->status->value) }}
                                    </span>
                                    @if($subscription->is_trial)
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-700">TRIAL</span>
                                    @endif
                                    <span class="text-xs text-slate-400 font-medium">Expires on {{ $subscription->ends_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <button class="px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition duration-200">
                            View Details
                        </button>
                    </div>
                @empty
                    <div class="bg-white p-12 rounded-3xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-300">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">No active subscriptions</h3>
                        <p class="text-slate-500 max-w-xs mt-1">Get started with our premium maintenance packages today.</p>
                        <a href="/catalog" class="mt-4 text-sm font-bold text-indigo-600 hover:text-indigo-500 transition duration-200">
                            Upgrade now &rarr;
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Stats & Account Info -->
            <div class="space-y-8">
                <!-- User Card -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-8 rounded-3xl text-white shadow-xl shadow-slate-200">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center text-xl font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Welcome back,</p>
                            <h3 class="text-xl font-bold tracking-tight">{{ auth()->user()->name }}</h3>
                        </div>
                    </div>
                    
                    <div class="mt-8 space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Trial Usage</span>
                            <span class="font-bold">{{ auth()->user()->trial_used_at ? 'Used' : 'Available' }}</span>
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-1.5 mt-1 overflow-hidden">
                            <div class="bg-indigo-500 h-full {{ auth()->user()->trial_used_at ? 'w-full' : 'w-0' }} transition-all duration-1000"></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Placeholder -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h2 class="font-bold text-slate-900 mb-4">Recent Bookings</h2>
                    <div class="space-y-4">
                        <p class="text-sm text-slate-500 py-4 text-center border border-dashed border-slate-200 rounded-xl italic">No recent bookings found.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
