<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">Global Shipments Explorer</h1>
                <p class="text-sm text-slate-600 mt-1">Search, filter, and inspect shipment manifests & tracking status</p>
            </div>
            @if (Auth::user()->isAdmin() || Auth::user()->isOperator())
                <a href="{{ route('shipments.create') }}" class="inline-flex items-center px-4 py-2 bg-navy-900 hover:bg-navy-800 text-beige-100 font-medium text-sm rounded-xl shadow-md border border-navy-700 transition">
                    <svg class="w-4 h-4 mr-2 text-beige-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Create Shipment
                </a>
            @endif
        </div>
    </x-slot>

    <!-- Filters & Search Bar Card -->
    <div class="bg-white rounded-2xl p-6 border border-beige-300/60 shadow-sm mb-6">
        <form method="GET" action="{{ route('shipments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search Query -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Shipment #, cargo type, notes..." class="w-full text-sm rounded-xl border-beige-300 focus:border-navy-700 focus:ring-navy-700 placeholder-slate-400 pl-10">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Status</label>
                <select name="status" class="w-full text-sm rounded-xl border-beige-300 focus:border-navy-700 focus:ring-navy-700">
                    <option value="">All Statuses</option>
                    @foreach (['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Carrier Filter -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Carrier</label>
                <select name="carrier_id" class="w-full text-sm rounded-xl border-beige-300 focus:border-navy-700 focus:ring-navy-700">
                    <option value="">All Carriers</option>
                    @foreach ($carriers as $c)
                        <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Delay Filter & Action Buttons -->
            <div class="flex items-end space-x-2">
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Delay Status</label>
                    <select name="delay_status" class="w-full text-sm rounded-xl border-beige-300 focus:border-navy-700 focus:ring-navy-700">
                        <option value="">All Schedules</option>
                        <option value="ON_TIME" {{ request('delay_status') === 'ON_TIME' ? 'selected' : '' }}>On Time</option>
                        <option value="DELAYED" {{ request('delay_status') === 'DELAYED' ? 'selected' : '' }}>Delayed</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 bg-navy-900 text-beige-100 rounded-xl hover:bg-navy-800 text-sm font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('shipments.index') }}" class="px-3 py-2.5 bg-beige-200 text-slate-700 rounded-xl hover:bg-beige-300 text-sm font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Shipments Table -->
    <div class="bg-white rounded-2xl border border-beige-300/60 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-beige-100/70 text-navy-950 text-xs uppercase font-bold tracking-wider border-b border-beige-200">
                    <tr>
                        <th class="px-6 py-4">Shipment #</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Carrier</th>
                        <th class="px-6 py-4">Origin &rarr; Destination</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Schedule</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    @forelse ($shipments as $shipment)
                        <tr class="hover:bg-beige-50/50 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('shipments.show', $shipment) }}" class="font-mono font-bold text-navy-900 hover:text-navy-700 block text-base">
                                    {{ $shipment->shipment_number }}
                                </a>
                                <div class="text-xs text-slate-500 font-medium">{{ $shipment->cargo_type }} &bull; {{ number_format($shipment->total_weight_kg, 0) }} kg</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800">{{ $shipment->customer->name }}</span>
                                <div class="text-xs text-slate-500">{{ $shipment->customer->company_name ?? $shipment->customer->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-navy-950">{{ $shipment->carrier->name }}</span>
                                <span class="text-xs text-slate-400 font-mono block">{{ $shipment->carrier->code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800 flex items-center space-x-1.5">
                                    <span>{{ $shipment->origin->city }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    <span>{{ $shipment->destination->city }}</span>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">{{ $shipment->origin->code }} ({{ $shipment->origin->country }}) &rarr; {{ $shipment->destination->code }} ({{ $shipment->destination->country }})</div>
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
                                        On Time
                                    </span>
                                @endif
                                <div class="text-xs text-slate-500 mt-1">ETA: {{ $shipment->estimated_arrival_at?->format('d M Y') ?? $shipment->expected_arrival_at->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="p-1.5 rounded-lg text-navy-800 hover:bg-beige-100 transition" title="View Visibility Portal">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    @if (Auth::user()->isAdmin() || Auth::user()->isOperator())
                                        <a href="{{ route('shipments.edit', $shipment) }}" class="p-1.5 rounded-lg text-slate-600 hover:bg-beige-100 transition" title="Edit Shipment">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    @endif
                                    @if (Auth::user()->isAdmin())
                                        <form method="POST" action="{{ route('shipments.destroy', $shipment) }}" onsubmit="return confirm('Delete shipment {{ $shipment->shipment_number }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition" title="Delete Shipment">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                No shipments found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-beige-200">
            {{ $shipments->links() }}
        </div>
    </div>
</x-app-layout>
