<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">Notifications Center</h1>
                <p class="text-sm text-slate-600 mt-1">Operational alerts, milestone updates, and exception advisories</p>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-beige-200 hover:bg-beige-300 text-slate-700 font-semibold text-xs rounded-xl transition">
                    Mark All as Read
                </button>
            </form>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        @forelse ($notifications as $notif)
            <div class="p-5 rounded-2xl border transition {{ $notif->is_read ? 'bg-white border-beige-200' : 'bg-beige-50/80 border-navy-200 shadow-sm' }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3">
                        <div class="p-2 rounded-xl {{ str_contains($notif->type, 'EXCEPTION') ? 'bg-rose-100 text-rose-700' : 'bg-navy-100 text-navy-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h4 class="font-bold text-sm text-navy-950">{{ $notif->title }}</h4>
                                @if (!$notif->is_read)
                                    <span class="w-2 h-2 rounded-full bg-navy-700 inline-block"></span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-700 mt-1 leading-relaxed">{{ $notif->message }}</p>
                            <span class="text-[11px] font-mono text-slate-400 mt-2 block">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @if (!$notif->is_read)
                        <form method="POST" action="{{ route('notifications.read', $notif) }}">
                            @csrf
                            <button type="submit" class="text-xs text-navy-700 hover:text-navy-900 font-semibold px-2 py-1 rounded hover:bg-beige-100">
                                Dismiss
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center text-slate-400 border border-beige-200">
                You have no notifications at this time.
            </div>
        @endforelse

        <div class="p-4">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
