<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">Executive Visibility Dashboard</h1>
                <p class="text-sm text-slate-600 mt-1">Real-time global shipment performance & SLA monitoring</p>
            </div>
            @if (Auth::user()->isAdmin() || Auth::user()->isOperator())
                <div class="flex items-center space-x-3">
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center px-4 py-2 bg-navy-900 hover:bg-navy-800 text-beige-100 font-medium text-sm rounded-xl shadow-md border border-navy-700 transition">
                        <svg class="w-4 h-4 mr-2 text-beige-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Shipment
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <!-- Top KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
        <!-- Total Shipments -->
        <div class="bg-white rounded-2xl p-5 border border-beige-300/60 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Tracked</span>
                <span class="p-2 rounded-lg bg-navy-50 text-navy-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-navy-950">{{ $totalShipments }}</span>
                <span class="text-xs text-slate-500 ml-1">orders</span>
            </div>
        </div>

        <!-- In Transit -->
        <div class="bg-white rounded-2xl p-5 border border-beige-300/60 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">In Transit</span>
                <span class="p-2 rounded-lg bg-sky-50 text-sky-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-sky-900">{{ $inTransitCount }}</span>
                <span class="text-xs text-slate-500 ml-1">active</span>
            </div>
        </div>

        <!-- Delivered -->
        <div class="bg-white rounded-2xl p-5 border border-beige-300/60 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Delivered</span>
                <span class="p-2 rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-emerald-700">{{ $deliveredCount }}</span>
                <span class="text-xs text-slate-500 ml-1">complete</span>
            </div>
        </div>

        <!-- Delayed -->
        <div class="bg-white rounded-2xl p-5 border border-beige-300/60 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Delayed</span>
                <span class="p-2 rounded-lg bg-rose-50 text-rose-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-rose-700">{{ $delayedCount }}</span>
                <span class="text-xs text-slate-500 ml-1">avg {{ $avgDelayHours }}h</span>
            </div>
        </div>

        <!-- Open Exceptions -->
        <div class="bg-white rounded-2xl p-5 border border-beige-300/60 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Exceptions</span>
                <span class="p-2 rounded-lg bg-amber-50 text-amber-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-amber-700">{{ $openExceptionsCount }}</span>
                <span class="text-xs text-slate-500 ml-1">open cases</span>
            </div>
        </div>

        <!-- On-Time Rate -->
        <div class="bg-white rounded-2xl p-5 border border-beige-300/60 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">On-Time SLA</span>
                <span class="p-2 rounded-lg bg-teal-50 text-teal-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-navy-900">{{ $onTimeRate }}%</span>
                <span class="text-xs text-slate-500 ml-1">delivery</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Status Breakdown Donut Chart -->
        <div class="bg-white rounded-2xl p-6 border border-beige-300/60 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-navy-950 text-base">Shipment Lifecycle Distribution</h3>
                <span class="text-xs text-slate-500">Database actuals</span>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Carrier Volume Bar Chart -->
        <div class="bg-white rounded-2xl p-6 border border-beige-300/60 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-navy-950 text-base">Shipment Volume by Carrier</h3>
                <span class="text-xs text-slate-500">Active allocations</span>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="carrierChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Shipments Table -->
    <div class="bg-white rounded-2xl border border-beige-300/60 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-beige-200/60 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-navy-950 text-lg">Active Shipments Pipeline</h3>
                <p class="text-xs text-slate-500 mt-0.5">Most recently updated shipment orders</p>
            </div>
            <a href="{{ route('shipments.index') }}" class="text-sm font-semibold text-navy-700 hover:text-navy-900 flex items-center">
                View all shipments &rarr;
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-beige-100/70 text-navy-950 text-xs uppercase font-bold tracking-wider border-b border-beige-200">
                    <tr>
                        <th class="px-6 py-4">Shipment #</th>
                        <th class="px-6 py-4">Carrier</th>
                        <th class="px-6 py-4">Route</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Schedule & Delay</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    @forelse ($recentShipments as $shipment)
                        <tr class="hover:bg-beige-50/50 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('shipments.show', $shipment) }}" class="font-mono font-bold text-navy-900 hover:text-navy-700">
                                    {{ $shipment->shipment_number }}
                                </a>
                                <div class="text-xs text-slate-500">{{ $shipment->cargo_type }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-800">{{ $shipment->carrier->name }}</span>
                                <span class="text-xs text-slate-400 block font-mono">({{ $shipment->carrier->code }})</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-1.5 font-medium">
                                    <span>{{ $shipment->origin->city }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    <span>{{ $shipment->destination->city }}</span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $shipment->origin->code }} &rarr; {{ $shipment->destination->code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeColors = [
                                        'CREATED' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'BOOKED' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        'PICKED_UP' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'IN_TRANSIT' => 'bg-sky-100 text-sky-800 border-sky-300',
                                        'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeColors[$shipment->current_status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $shipment->current_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($shipment->delay_status === 'DELAYED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Delayed +{{ $shipment->delay_duration_hours }}h ({{ $shipment->delay_severity }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        On Schedule
                                    </span>
                                @endif
                                <div class="text-[11px] text-slate-500 mt-0.5">ETA: {{ $shipment->estimated_arrival_at?->format('M d, H:i') ?? $shipment->expected_arrival_at->format('M d, H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('shipments.show', $shipment) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-navy-900 text-beige-100 hover:bg-navy-800 shadow-sm transition">
                                    Track &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                No shipments found in database. Seed data or create a new shipment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Lifecycle Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($statusCounts)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($statusCounts)) !!},
                        backgroundColor: [
                            '#94a3b8', // CREATED (slate)
                            '#3b82f6', // BOOKED (blue)
                            '#f59e0b', // PICKED_UP (amber)
                            '#0284c7', // IN_TRANSIT (sky/navy)
                            '#10b981', // DELIVERED (emerald)
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                    }
                }
            });

            // Carrier Volume Chart
            const carrierCtx = document.getElementById('carrierChart').getContext('2d');
            new Chart(carrierCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($carrierCounts)) !!},
                    datasets: [{
                        label: 'Shipment Count',
                        data: {!! json_encode(array_values($carrierCounts)) !!},
                        backgroundColor: '#1e3a8a',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { font: { size: 11 } } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
