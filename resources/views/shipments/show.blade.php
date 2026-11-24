<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <span class="font-mono text-2xl font-black text-navy-950 tracking-tight">{{ $shipment->shipment_number }}</span>
                    @php
                        $badgeColors = [
                            'CREATED' => 'bg-slate-100 text-slate-800 border-slate-300',
                            'BOOKED' => 'bg-blue-50 text-blue-800 border-blue-300',
                            'PICKED_UP' => 'bg-amber-50 text-amber-800 border-amber-300',
                            'IN_TRANSIT' => 'bg-sky-100 text-sky-800 border-sky-300',
                            'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        ];
                    @endphp
                    <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $badgeColors[$shipment->current_status] ?? 'bg-gray-100' }}">
                        {{ $shipment->current_status }}
                    </span>
                    @if ($shipment->delay_status === 'DELAYED')
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-300">
                            DELAYED +{{ $shipment->delay_duration_hours }}h ({{ $shipment->delay_severity }})
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                            ON SCHEDULE
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Carrier: <strong class="text-navy-900">{{ $shipment->carrier->name }}</strong> ({{ $shipment->carrier->code }}) &bull;
                    Customer: <strong class="text-navy-900">{{ $shipment->customer->name }}</strong> &bull;
                    Cargo: {{ $shipment->cargo_type }}
                </p>
            </div>

            <!-- Action Controls for Operators & Admin -->
            @if (Auth::user()->isAdmin() || Auth::user()->isOperator())
                <div class="flex flex-wrap items-center gap-2">
                    @if (!$shipment->isTerminal())
                        <!-- Next State Transition Button -->
                        @php
                            $nextAllowed = \App\Models\Shipment::VALID_TRANSITIONS[$shipment->current_status] ?? [];
                        @endphp
                        @if (!empty($nextAllowed))
                            <button onclick="document.getElementById('modal-transition').classList.remove('hidden')" class="px-3.5 py-2 bg-navy-900 text-beige-100 hover:bg-navy-800 rounded-xl text-xs font-bold shadow-sm transition">
                                Next Status: {{ $nextAllowed[0] }} &rarr;
                            </button>
                        @endif

                        <button onclick="document.getElementById('modal-tracking').classList.remove('hidden')" class="px-3.5 py-2 bg-beige-200 text-navy-950 hover:bg-beige-300 rounded-xl text-xs font-bold transition">
                            + Add Waypoint
                        </button>

                        <button onclick="document.getElementById('modal-exception').classList.remove('hidden')" class="px-3.5 py-2 bg-rose-100 text-rose-900 hover:bg-rose-200 border border-rose-200 rounded-xl text-xs font-bold transition">
                            ! Raise Exception
                        </button>
                    @else
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-semibold border border-emerald-200">
                            Terminal State (DELIVERED)
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>

    <!-- Lifecycle Status Stepper (UAS Brief slide 6) -->
    <div class="bg-white rounded-2xl p-6 border border-beige-300/60 shadow-sm mb-6">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Shipment Lifecycle Progression</h2>
        @php
            $steps = ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];
            $currentIndex = array_search($shipment->current_status, $steps);
        @endphp
        <div class="relative flex items-center justify-between">
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-beige-200 w-full z-0"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-navy-800 z-0 transition-all duration-500" style="width: {{ ($currentIndex / (count($steps) - 1)) * 100 }}%;"></div>

            @foreach ($steps as $index => $step)
                @php
                    $isPassed = $index < $currentIndex;
                    $isCurrent = $index === $currentIndex;
                @endphp
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs border-2 transition shadow-sm
                        {{ $isCurrent ? 'bg-navy-900 text-beige-100 border-navy-700 ring-4 ring-navy-100' : ($isPassed ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-white text-slate-400 border-beige-300') }}">
                        @if ($isPassed)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <span class="text-[11px] font-bold mt-2 tracking-tight {{ $isCurrent ? 'text-navy-950 font-extrabold' : ($isPassed ? 'text-slate-700' : 'text-slate-400') }}">
                        {{ $step }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Active Exception Alert (if any) -->
    @if ($shipment->openExceptions->count() > 0)
        <div class="mb-6 p-5 rounded-2xl bg-amber-50 border border-amber-300 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold text-amber-900">Active Operational Exceptions ({{ $shipment->openExceptions->count() }})</h3>
                        <p class="text-xs text-amber-800 mt-1">This shipment is currently flagged with operational anomalies requiring coordination.</p>
                        <div class="mt-3 space-y-2">
                            @foreach ($shipment->openExceptions as $exc)
                                <div class="bg-white/80 p-3 rounded-xl border border-amber-200 flex items-center justify-between">
                                    <div>
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-rose-100 text-rose-800">{{ $exc->severity }} Severity</span>
                                        <span class="font-bold text-xs text-navy-950 ml-1.5">{{ $exc->exception_type }}</span>
                                        <span class="text-xs text-slate-600 block mt-0.5">{{ $exc->root_cause }}</span>
                                    </div>
                                    @if (Auth::user()->isAdmin() || Auth::user()->isOperator())
                                        <button onclick="openResolveModal('{{ $exc->id }}', '{{ $exc->exception_type }}')" class="px-3 py-1 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition">
                                            Resolve
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Grid: Map & Route on Left, Weather & Overview on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Interactive Leaflet Map (2 Columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-beige-300/60 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-navy-950 text-base">Global Route & Waypoint Visibility</h3>
                    <p class="text-xs text-slate-500">Leaflet.js + OpenStreetMap dynamic tracking route</p>
                </div>
                <div class="text-xs font-mono text-slate-400">
                    {{ $shipment->origin->code }} &rarr; {{ $shipment->destination->code }}
                </div>
            </div>

            <!-- Leaflet Map Canvas -->
            <div id="shipmentMap" class="w-full h-96 rounded-xl border border-beige-200 z-10"></div>

            <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-navy-900 inline-block mr-1.5"></span> Origin: {{ $shipment->origin->city }}</span>
                    <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-600 inline-block mr-1.5"></span> Waypoint Events</span>
                    <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-amber-600 inline-block mr-1.5"></span> Destination: {{ $shipment->destination->city }}</span>
                </div>
                <span>Map data &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline">OpenStreetMap</a> contributors</span>
            </div>
        </div>

        <!-- Right Side: Live Weather Integration & Schedule Summary -->
        <div class="space-y-6">
            <!-- External Weather API Widget (UAS Brief slide 9) -->
            <div class="bg-gradient-to-br from-navy-900 to-navy-950 text-beige-100 rounded-2xl p-5 shadow-sm border border-navy-800">
                <div class="flex items-center justify-between mb-3 border-b border-navy-800 pb-3">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-beige-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                        <h4 class="font-bold text-sm text-white">Live Port Weather</h4>
                    </div>
                    <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded bg-navy-800 text-beige-300 border border-navy-700">Open-Meteo API</span>
                </div>

                <div class="text-xs text-slate-300 mb-2">
                    Monitoring: <strong class="text-white">{{ $weatherLocation->name }}</strong> ({{ $weatherLocation->city }}, {{ $weatherLocation->country }})
                </div>

                @if ($weatherData['available'])
                    <div class="flex items-center justify-between my-3">
                        <div>
                            <div class="text-3xl font-black text-white tracking-tight">{{ $weatherData['temperature'] }}&deg;C</div>
                            <div class="text-xs text-beige-300 font-medium">{{ $weatherData['condition'] }}</div>
                        </div>
                        <div class="text-right text-xs text-slate-300 space-y-1">
                            <div>Wind: <strong class="text-white">{{ $weatherData['wind_speed'] }} km/h</strong></div>
                            <div>Observation: <span class="text-slate-400 font-mono">{{ \Carbon\Carbon::parse($weatherData['time'])->format('H:i T') }}</span></div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-400 italic">
                        Real-time external atmospheric observation via Laravel HTTP Client with timeout resilience.
                    </div>
                @else
                    <div class="p-3 bg-navy-900/60 rounded-xl border border-navy-800 text-xs text-amber-300">
                        {{ $weatherData['message'] ?? 'Live weather data currently unavailable.' }}
                    </div>
                @endif
            </div>

            <!-- Schedule & Delay Analysis -->
            <div class="bg-white rounded-2xl p-5 border border-beige-300/60 shadow-sm">
                <h4 class="font-bold text-sm text-navy-950 mb-3 border-b border-beige-200 pb-2">Schedule & ETA Engine</h4>
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Departure (Expected):</span>
                        <span class="font-semibold text-slate-800 font-mono">{{ $shipment->expected_departure_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Arrival (Expected):</span>
                        <span class="font-semibold text-slate-800 font-mono">{{ $shipment->expected_arrival_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Arrival (Estimated ETA):</span>
                        <span class="font-semibold text-navy-900 font-mono">{{ $shipment->estimated_arrival_at?->format('d M Y, H:i') ?? 'Same as Expected' }}</span>
                    </div>
                    <div class="pt-2 border-t border-beige-100 flex justify-between items-center">
                        <span class="text-slate-500">Delay Evaluation:</span>
                        @if ($shipment->delay_status === 'DELAYED')
                            <span class="font-bold text-rose-700 font-mono bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                +{{ $shipment->delay_duration_hours }} hrs &bull; {{ $shipment->delay_severity }}
                            </span>
                        @else
                            <span class="font-bold text-emerald-700 font-mono bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                0 hrs &bull; ON TIME
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-400 bg-beige-50 p-2.5 rounded-lg border border-beige-200">
                        Rule: Evaluated deterministically against configured threshold (Low &le; {{ config('cargotrace.delay_thresholds.low', 6) }}h, Med &le; {{ config('cargotrace.delay_thresholds.medium', 24) }}h).
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chronological Tracking Timeline & Manifest Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chronological Timeline (2 Columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-beige-300/60 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-navy-950 text-base">Chronological Tracking Journey</h3>
                    <p class="text-xs text-slate-500">Append-only audit trail of physical and operational events</p>
                </div>
                <span class="text-xs font-bold text-navy-700 bg-navy-50 px-2.5 py-1 rounded-full border border-navy-100">
                    {{ $shipment->trackingEvents->count() }} milestones
                </span>
            </div>

            <!-- Timeline Elements -->
            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-beige-200">
                @forelse ($shipment->trackingEvents as $event)
                    <div class="relative group">
                        <!-- Dot -->
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full border-2 bg-white flex items-center justify-center transition
                            {{ $event->is_exception ? 'border-rose-500 text-rose-500' : 'border-navy-900 text-navy-900' }}">
                            <div class="w-2 h-2 rounded-full {{ $event->is_exception ? 'bg-rose-500' : 'bg-navy-900' }}"></div>
                        </div>

                        <!-- Content Card -->
                        <div class="bg-beige-50/60 p-4 rounded-xl border border-beige-200 hover:border-beige-300 transition">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-xs text-navy-950">{{ $event->event_type }}</span>
                                    @if ($event->is_exception)
                                        <span class="px-1.5 py-0.5 text-[9px] uppercase font-bold rounded bg-rose-100 text-rose-800">Exception</span>
                                    @endif
                                </div>
                                <span class="text-[11px] font-mono text-slate-500">{{ $event->event_time->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-xs text-slate-700 leading-relaxed">{{ $event->description }}</p>
                            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                                <span>
                                    Location: <strong class="text-slate-600">{{ $event->location?->name ?? 'In-Transit Route' }}</strong>
                                    @if ($event->latitude && $event->longitude)
                                        ({{ $event->latitude }}, {{ $event->longitude }})
                                    @endif
                                </span>
                                <span>Logged by: {{ $event->creator->name ?? 'System' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4">No tracking events recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Manifest Cargo Items Breakdown -->
        <div class="bg-white rounded-2xl p-6 border border-beige-300/60 shadow-sm">
            <h3 class="font-bold text-navy-950 text-base mb-1">Cargo Manifest</h3>
            <p class="text-xs text-slate-500 mb-4">Packaged inventory breakdown & weights</p>

            <div class="space-y-3">
                @forelse ($shipment->items as $item)
                    <div class="p-3 bg-beige-50/60 rounded-xl border border-beige-200">
                        <div class="flex items-start justify-between">
                            <span class="font-semibold text-xs text-navy-950">{{ $item->item_description }}</span>
                            <span class="text-xs font-mono font-bold text-slate-600">x{{ $item->quantity }}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500 font-mono">
                            <span>HS: {{ $item->hs_code ?? 'N/A' }}</span>
                            <span>{{ number_format($item->weight_kg, 1) }} kg &bull; {{ number_format($item->volume_cbm, 2) }} CBM</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">No individual cargo items registered.</p>
                @endforelse
            </div>

            <div class="mt-4 pt-3 border-t border-beige-200 text-xs flex justify-between font-bold text-navy-950">
                <span>Total Weight:</span>
                <span>{{ number_format($shipment->total_weight_kg, 1) }} kg</span>
            </div>
            <div class="mt-1 flex justify-between text-xs font-bold text-navy-950">
                <span>Total Volume:</span>
                <span>{{ number_format($shipment->total_volume_cbm, 2) }} CBM</span>
            </div>
        </div>
    </div>

    <!-- Modals for Operator Actions -->
    @if (Auth::user()->isAdmin() || Auth::user()->isOperator())
        <!-- Modal: Transition Status -->
        <div id="modal-transition" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-beige-300">
                <h3 class="text-base font-bold text-navy-950 mb-2">Transition Shipment Status</h3>
                <p class="text-xs text-slate-500 mb-4">Advance lifecycle under state machine validation.</p>
                <form method="POST" action="{{ route('shipments.transition', $shipment) }}">
                    @csrf
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Target Status</label>
                            <select name="status" class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                                @foreach (\App\Models\Shipment::VALID_TRANSITIONS[$shipment->current_status] ?? [] as $target)
                                    <option value="{{ $target }}">{{ $target }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Milestone Description</label>
                            <textarea name="description" rows="2" class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700" placeholder="e.g. Container loaded on vessel at Terminal 2."></textarea>
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Location Hub</label>
                            <select name="location_id" class="w-full text-xs rounded-xl border-beige-300 focus:ring-navy-700">
                                <option value="">Select Location (Optional)</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('modal-transition').classList.add('hidden')" class="px-4 py-2 bg-beige-200 text-slate-700 rounded-xl text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-navy-900 text-beige-100 rounded-xl text-xs font-semibold">Confirm Transition</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Add Tracking Event -->
        <div id="modal-tracking" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-beige-300">
                <h3 class="text-base font-bold text-navy-950 mb-2">Record Tracking Event</h3>
                <p class="text-xs text-slate-500 mb-4">Append historical waypoint without changing status.</p>
                <form method="POST" action="{{ route('shipments.tracking.store', $shipment) }}">
                    @csrf
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Event Type</label>
                            <input type="text" name="event_type" required class="w-full text-xs rounded-xl border-beige-300" placeholder="e.g. CUSTOMS_INSPECTION_CLEARED">
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Location Hub</label>
                            <select name="location_id" class="w-full text-xs rounded-xl border-beige-300">
                                <option value="">None / Open Sea</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Description</label>
                            <textarea name="description" required rows="2" class="w-full text-xs rounded-xl border-beige-300" placeholder="Detailed physical status notes..."></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('modal-tracking').classList.add('hidden')" class="px-4 py-2 bg-beige-200 text-slate-700 rounded-xl text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-navy-900 text-beige-100 rounded-xl text-xs font-semibold">Save Event</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Raise Exception -->
        <div id="modal-exception" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-200">
                <h3 class="text-base font-bold text-rose-900 mb-2">Raise Operational Exception</h3>
                <p class="text-xs text-slate-500 mb-4">Alert customer and record issue in compliance log.</p>
                <form method="POST" action="{{ route('shipments.exceptions.store', $shipment) }}">
                    @csrf
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Exception Type</label>
                            <select name="exception_type" required class="w-full text-xs rounded-xl border-beige-300">
                                <option value="CUSTOMS_HOLD">CUSTOMS_HOLD</option>
                                <option value="WEATHER_DELAY">WEATHER_DELAY</option>
                                <option value="PORT_CONGESTION">PORT_CONGESTION</option>
                                <option value="DAMAGED">DAMAGED</option>
                                <option value="MISROUTED">MISROUTED</option>
                                <option value="DOCUMENTATION_ISSUE">DOCUMENTATION_ISSUE</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Severity</label>
                            <select name="severity" required class="w-full text-xs rounded-xl border-beige-300">
                                <option value="LOW">LOW</option>
                                <option value="MEDIUM" selected>MEDIUM</option>
                                <option value="HIGH">HIGH</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Root Cause / Findings</label>
                            <textarea name="root_cause" required rows="2" class="w-full text-xs rounded-xl border-beige-300" placeholder="Specific reason for hold or delay..."></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('modal-exception').classList.add('hidden')" class="px-4 py-2 bg-beige-200 text-slate-700 rounded-xl text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-rose-700 text-white rounded-xl text-xs font-semibold">Dispatch Alert</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Resolve Exception -->
        <div id="modal-resolve" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-emerald-200">
                <h3 class="text-base font-bold text-navy-950 mb-2">Resolve Exception</h3>
                <p id="resolve-modal-subtitle" class="text-xs text-slate-500 mb-4"></p>
                <form id="resolve-form" method="POST" action="">
                    @csrf
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold uppercase text-slate-600 mb-1">Resolution Notes</label>
                            <textarea name="resolution_notes" required rows="3" class="w-full text-xs rounded-xl border-beige-300" placeholder="Explain how the issue was resolved..."></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('modal-resolve').classList.add('hidden')" class="px-4 py-2 bg-beige-200 text-slate-700 rounded-xl text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-semibold">Mark as Resolved</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
    <style>
        /* Pulsing animation for delivery point */
        @keyframes deliveryPulse {
            0%   { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.6); }
            70%  { box-shadow: 0 0 0 14px rgba(217, 119, 6, 0); }
            100% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0); }
        }
        .delivery-pin-inner {
            animation: deliveryPulse 1.8s infinite;
        }

        /* Origin pin bounce */
        @keyframes originBounce {
            0%, 100% { transform: rotate(-45deg) translateY(0); }
            50%       { transform: rotate(-45deg) translateY(-4px); }
        }
        .origin-pin-wrap {
            animation: originBounce 2.4s ease-in-out infinite;
        }

        /* Waypoint pop */
        @keyframes waypointPop {
            0%   { transform: scale(0); opacity: 0; }
            70%  { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
        .waypoint-dot {
            animation: waypointPop 0.4s ease forwards;
        }

        /* Leaflet popup styling override */
        .leaflet-popup-content b { color: #0f172a; }
        .popup-badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            margin-top: 4px;
        }
    </style>
    <script>
        function openResolveModal(exceptionId, type) {
            document.getElementById('resolve-modal-subtitle').innerText = 'Resolving ' + type + ' issue.';
            document.getElementById('resolve-form').action = '/shipments/{{ $shipment->id }}/exceptions/' + exceptionId + '/resolve';
            document.getElementById('modal-resolve').classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', function () {
            // ── Coordinates ──────────────────────────────────────────────
            const originLat = {{ $shipment->origin->latitude }};
            const originLng = {{ $shipment->origin->longitude }};
            const destLat   = {{ $shipment->destination->latitude }};
            const destLng   = {{ $shipment->destination->longitude }};

            // ── Map init ─────────────────────────────────────────────────
            const map = L.map('shipmentMap').setView([originLat, originLng], 4);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // ── Route coordinate list ─────────────────────────────────────
            const routePoints = [[originLat, originLng]];

            // ── ORIGIN custom pin (dark navy) ─────────────────────────────
            const originIcon = L.divIcon({
                className: '',
                html: `<div class="origin-pin-wrap" style="
                            width:32px; height:32px;
                            background:#0f172a;
                            border:3px solid #ffffff;
                            border-radius:50% 50% 50% 0;
                            transform:rotate(-45deg);
                            box-shadow:0 4px 12px rgba(0,0,0,0.35);
                            display:flex; align-items:center; justify-content:center;">
                        <svg style="transform:rotate(45deg);width:14px;height:14px;" fill="white" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z
                                     m0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5
                                     2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -34]
            });

            L.marker([originLat, originLng], { icon: originIcon })
                .addTo(map)
                .bindPopup(`
                    <div style="min-width:160px;">
                        <b style="font-size:12px;">🏭 ORIGIN / PICKUP</b><br>
                        <span style="font-size:13px;font-weight:700;color:#0f172a;">{{ $shipment->origin->name }}</span><br>
                        <span style="font-size:11px;color:#64748b;">{{ $shipment->origin->city }}, {{ $shipment->origin->country }}</span><br>
                        <span class="popup-badge" style="background:#e2e8f0;color:#0f172a;">PORT: {{ $shipment->origin->port_code ?? 'N/A' }}</span>
                    </div>`);

            // ── DESTINATION / DELIVERY custom pin (amber + pulse) ─────────
            const deliveryIcon = L.divIcon({
                className: '',
                html: `<div style="position:relative;width:40px;height:40px;">
                        <!-- Pulsing ring -->
                        <div style="
                            position:absolute; top:50%; left:50%;
                            transform:translate(-50%,-50%);
                            width:40px; height:40px;
                            border-radius:50%;
                            background:rgba(217,119,6,0.18);
                            animation:deliveryPulse 1.8s infinite;"></div>
                        <!-- Pin body -->
                        <div class="delivery-pin-inner" style="
                            position:absolute; top:4px; left:4px;
                            width:32px; height:32px;
                            background:linear-gradient(135deg,#f59e0b,#d97706);
                            border:3px solid #ffffff;
                            border-radius:50% 50% 50% 0;
                            transform:rotate(-45deg);
                            box-shadow:0 4px 14px rgba(217,119,6,0.55);
                            display:flex; align-items:center; justify-content:center;">
                            <svg style="transform:rotate(45deg);width:15px;height:15px;" fill="white" viewBox="0 0 24 24">
                                <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34
                                         3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83
                                         0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5
                                         1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5
                                         1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                            </svg>
                        </div>
                    </div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -42]
            });

            const isDelivered = '{{ $shipment->current_status }}' === 'DELIVERED';

            L.marker([destLat, destLng], { icon: deliveryIcon })
                .addTo(map)
                .bindPopup(`
                    <div style="min-width:180px;">
                        <b style="font-size:12px;">📦 DELIVERY POINT</b><br>
                        <span style="font-size:13px;font-weight:700;color:#0f172a;">{{ $shipment->destination->name }}</span><br>
                        <span style="font-size:11px;color:#64748b;">{{ $shipment->destination->city }}, {{ $shipment->destination->country }}</span><br>
                        <span class="popup-badge" style="background:#e2e8f0;color:#0f172a;">PORT: {{ $shipment->destination->port_code ?? 'N/A' }}</span><br>
                        <span class="popup-badge" style="${isDelivered ? 'background:#d1fae5;color:#065f46;' : 'background:#fef3c7;color:#92400e;'}">
                            ${isDelivered ? '✅ DELIVERED' : '🚚 AWAITING DELIVERY'}
                        </span>
                    </div>`);

            // ── Tracking EVENT waypoints ───────────────────────────────────
            @foreach ($shipment->trackingEvents as $ev)
                @if ($ev->latitude && $ev->longitude)
                    routePoints.push([{{ $ev->latitude }}, {{ $ev->longitude }}]);

                    const wpIcon_{{ $loop->index }} = L.divIcon({
                        className: '',
                        html: `<div class="waypoint-dot" style="
                            width:14px; height:14px;
                            background:{{ $ev->is_exception ? '#e11d48' : '#059669' }};
                            border:2px solid white;
                            border-radius:50%;
                            box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>`,
                        iconSize: [14, 14],
                        iconAnchor: [7, 7],
                        popupAnchor: [0, -10]
                    });

                    L.marker([{{ $ev->latitude }}, {{ $ev->longitude }}], { icon: wpIcon_{{ $loop->index }} })
                        .addTo(map)
                        .bindPopup(`
                            <div style="min-width:160px;">
                                <span class="popup-badge" style="{{ $ev->is_exception ? 'background:#fce7f3;color:#9d174d;' : 'background:#d1fae5;color:#065f46;' }}">
                                    {{ $ev->is_exception ? '⚠️ EXCEPTION' : '✅ CHECKPOINT' }}
                                </span><br>
                                <b style="font-size:12px;">{{ $ev->event_type }}</b><br>
                                <span style="font-size:11px;color:#64748b;">{{ $ev->event_time->format('d M Y H:i') }}</span><br>
                                <span style="font-size:12px;">{{ addslashes($ev->description) }}</span>
                            </div>`);
                @endif
            @endforeach

            routePoints.push([destLat, destLng]);

            // ── Route polyline ─────────────────────────────────────────────
            const polyline = L.polyline(routePoints, {
                color: '#1e3a8a',
                weight: 3,
                opacity: 0.75,
                dashArray: '8, 6'
            }).addTo(map);

            // ── Map legend ────────────────────────────────────────────────
            const legend = L.control({ position: 'bottomright' });
            legend.onAdd = function () {
                const div = L.DomUtil.create('div');
                div.style.cssText = 'background:white;padding:10px 14px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);font-size:11px;line-height:1.9;min-width:140px;';
                div.innerHTML = `
                    <div style="font-weight:700;color:#0f172a;margin-bottom:4px;font-size:12px;">📍 Map Legend</div>
                    <div><span style="display:inline-block;width:12px;height:12px;background:#0f172a;border-radius:50% 50% 50% 0;transform:rotate(-45deg);vertical-align:middle;margin-right:6px;"></span>Origin / Pickup</div>
                    <div><span style="display:inline-block;width:12px;height:12px;background:#d97706;border-radius:50% 50% 50% 0;transform:rotate(-45deg);vertical-align:middle;margin-right:6px;"></span>Delivery Point</div>
                    <div><span style="display:inline-block;width:11px;height:11px;background:#059669;border-radius:50%;vertical-align:middle;margin-right:6px;"></span>Checkpoint</div>
                    <div><span style="display:inline-block;width:11px;height:11px;background:#e11d48;border-radius:50%;vertical-align:middle;margin-right:6px;"></span>Exception</div>
                    <div><span style="display:inline-block;width:22px;height:3px;background:#1e3a8a;border-top:2px dashed #1e3a8a;vertical-align:middle;margin-right:6px;"></span>Route</div>`;
                return div;
            };
            legend.addTo(map);

            // ── Fit map bounds ─────────────────────────────────────────────
            map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
        });
    </script>
    @endpush
</x-app-layout>
