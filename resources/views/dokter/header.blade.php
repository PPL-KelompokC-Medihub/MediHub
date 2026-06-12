{{-- Shared Doctor Header Row --}}
<div class="mediq-header-row">
    <div class="mediq-user-chip">
        @if(!blank($dokter->profile_pict ?? null))
            <img src="{{ asset('storage/' . $dokter->profile_pict) }}"
                 alt="Avatar"
                 class="mediq-avatar" />
        @else
            <img src="https://ui-avatars.com/api/?name={{ urlencode($dokter->name ?? Auth::user()->name ?? 'Dokter') }}&background=6aa4ef&color=fff&size=96"
                 alt="Avatar"
                 class="mediq-avatar" />
        @endif
        <div>
            <p class="mediq-user-name">
                Halo, dr {{ $dokter->name ?? Auth::user()->name ?? 'Dokter' }}
                <span class="greeting-emoji" style="font-size: 16px; margin-left: 2px;">👋</span>
            </p>
            <p class="doctor-greeting-subtitle">Bagaimana kabarmu?</p>
        </div>
    </div>
    <div class="mediq-header-actions">
        @if(Route::currentRouteName() === 'dokter.riwayat')
            <form action="{{ route('dokter.riwayat') }}" method="GET" class="mediq-search-wrap" style="margin: 0;">
                <input
                    type="text"
                    name="search"
                    class="mediq-search-input"
                    placeholder="Cari..."
                    value="{{ $search ?? '' }}"
                />
                @if(!empty($date))
                    <input type="hidden" name="date" value="{{ $date }}" />
                @endif
                <button type="submit" style="display: none;"></button>
                <svg class="mediq-search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
            </form>
        @else
            <div class="mediq-search-wrap">
                <input type="text" class="mediq-search-input" placeholder="Cari..." />
                <svg class="mediq-search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
            </div>
        @endif
        <button class="mediq-icon-btn" style="position: relative;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="mediq-bell-dot"></span>
        </button>
    </div>
</div>
