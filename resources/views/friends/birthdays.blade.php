@extends('layouts.friend')

@section('content')
<style>
    .birthdays-page {
        background: var(--sb-hover);
        min-height: 100vh;
        padding: 24px 0;
        color: var(--sb-text);
        border-radius: 10px;
    }

    .bday-shell {
        max-width: 680px;
        margin: 0 auto;
    }

    .bday-section-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--sb-text);
        margin: 4px 0 14px 4px;
    }

    .bday-card {
        background: var(--sb-bg);
        border: 1px solid var(--sb-border);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 8px;
    }

    .bday-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bday-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        background: var(--sb-avatar-fallback-bg);
    }

    .bday-avatar-fallback {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        flex-shrink: 0;
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        text-decoration: none !important;
        line-height: 1;
    }

    .bday-name {
        font-weight: 600;
        color: var(--sb-text);
        text-decoration: none;
    }

    .bday-name:hover {
        text-decoration: underline;
        color: var(--sb-text);
    }

    .bday-meta {
        font-size: 0.82rem;
        color: var(--sb-text-secondary);
    }

    .bday-age {
        font-size: 0.82rem;
        color: var(--sb-text-secondary);
        margin-left: auto;
        white-space: nowrap;
    }

    .btn-fb {
        background: var(--sb-hover);
        color: var(--sb-text);
        border: 1px solid var(--sb-border);
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .btn-fb:hover {
        background: var(--sb-border);
        color: var(--sb-text);
    }

    .btn-fb-primary {
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text);
        border: 1px solid var(--sb-avatar-fallback-bg);
    }

    .btn-fb-primary:hover {
        opacity: 0.9;
        color: var(--sb-avatar-fallback-text);
    }

    .today-badge {
        display: inline-block;
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text);
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 4px;
        padding: 2px 8px;
        margin-left: 6px;
    }

    .tomorrow-badge {
        display: inline-block;
        background: var(--sb-hover);
        color: var(--sb-text-secondary);
        border: 1px solid var(--sb-border);
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 4px;
        padding: 2px 8px;
        margin-left: 6px;
    }

    .quick-messages {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
        padding-left: 60px;
    }

    .quick-msg-btn {
        background: var(--sb-hover);
        color: var(--sb-text);
        border: 1px solid var(--sb-border);
        border-radius: 16px;
        padding: 7px 14px;
        font-size: 0.85rem;
        text-align: left;
    }

    .quick-msg-btn:hover {
        background: var(--sb-border);
    }

    .quick-composer {
        display: none;
        padding-left: 60px;
        margin-top: 10px;
    }

    .quick-composer.active {
        display: flex;
        gap: 8px;
    }

    .quick-composer textarea {
        flex: 1;
        resize: none;
        background: var(--sb-hover);
        border: 1px solid var(--sb-border);
        border-radius: 8px;
        color: var(--sb-text);
        padding: 8px 12px;
        font-size: 0.85rem;
    }

    .empty-state {
        color: var(--sb-text-secondary);
        text-align: center;
        padding: 40px 16px;
        font-size: 0.9rem;
    }

    .month-group {
        background: var(--sb-bg);
        border: 1px solid var(--sb-border);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
    }

    .month-group-title {
        font-weight: 700;
        color: var(--sb-text);
        font-size: 1rem;
        margin-bottom: 10px;
    }

    .month-group-subtitle {
        color: var(--sb-text-secondary);
        font-size: 0.82rem;
        margin-bottom: 14px;
    }

    .month-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
        gap: 10px;
    }

    .month-grid-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
        background: var(--sb-avatar-fallback-bg);
    }

    .month-grid-avatar-fallback {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none !important;
        line-height: 1;
    }

    .month-grid-link {
        display: block;
        text-decoration: none !important;
    }

    .month-grid-link:hover .month-grid-avatar-fallback {
        opacity: 0.85;
    }

    .bday-row + .bday-row-divider {
        border-color: var(--sb-border) !important;
    }
</style>

<div class="birthdays-page">
    <div class="bday-shell">

        {{-- ================= UPCOMING (friends whose birthday is coming up) ================= --}}
        @if ($upcoming->isNotEmpty())
            <div class="bday-section-title">{{ __('Upcoming friend birthdays') }}</div>

            <div class="bday-card">
                @foreach ($upcoming as $i => $data)
                    <div class="bday-row {{ $i > 0 ? 'mt-3 pt-3 border-top' : '' }}" style="{{ $i > 0 ? 'border-color:var(--sb-border)!important;' : '' }}">
                        @include('friends.bday-avatar', ['friend' => $data['user']])

                        <div>
                            <a href="" class="bday-name">
                                {{ $data['user']->first_name }} {{ $data['user']->last_name }}
                            </a>
                            @if ($data['is_today'])
                                <span class="today-badge">{{ __('TODAY') }}</span>
                            @elseif ($data['days_until'] === 1)
                                <span class="tomorrow-badge">{{ __('TOMORROW') }}</span>
                            @endif
                            <div class="bday-meta">
                                {{ $data['dob']->format('j F') }} &middot;
                                {{ __('Turns :age', ['age' => $data['turning_age']]) }}
                            </div>
                        </div>

                        <div class="bday-age">
                            @if ($data['is_today'])
                                {{ __('Today') }}
                            @elseif ($data['days_until'] === 1)
                                {{ __('Tomorrow') }}
                            @else
                                {{ __('In :n days', ['n' => $data['days_until']]) }}
                            @endif
                        </div>

                        @if ($data['is_today'])
                            <button type="button" class="btn-fb btn-fb-primary toggle-composer" data-target="upcoming-{{ $i }}">
                                {{ __('Message') }}
                            </button>
                        @else
                            <a href="#" class="btn-fb" data-open-messenger
                                data-friend-uuid="{{ $data['user']->uuid }}"
                                data-friend-name="{{ $data['user']->first_name }} {{ $data['user']->last_name }}"
                                data-friend-avatar="{{ $data['user']->avatar ? asset('storage/'.$data['user']->avatar) : '' }}"
                                data-friend-initial="{{ strtoupper(substr($data['user']->first_name, 0, 1)) }}">
                                    {{ __('Message') }}
                            </a>
                        @endif
                    </div>

                    @if ($data['is_today'])
                        @include('friends.quick-birthday-composer', ['id' => 'upcoming-'.$i, 'friend' => $data['user']])
                    @endif
                @endforeach
            </div>
        @endif

        {{-- ================= RECENT (friends whose birthday just passed) {{ route('conversations.start', $data['user']) }} ================= --}}
        <div class="bday-section-title {{ $upcoming->isNotEmpty() ? 'mt-4' : '' }}">{{ __('Recent friend birthdays') }}</div>

        @if ($recent->isEmpty() && $upcoming->isEmpty())
            <div class="bday-card empty-state">
                {{ __("No birthdays to show right now. When your friends' birthdays come up, you'll see them here.") }}
            </div>
        @elseif ($recent->isEmpty())
            <div class="bday-card empty-state">
                {{ __('No recent birthdays in the last 7 days.') }}
            </div>
        @else
            @foreach ($recent as $i => $data)
                <div class="bday-card">
                    <div class="bday-row">
                        @include('friends.bday-avatar', ['friend' => $data['user']])

                        <div>
                            <a href="" class="bday-name">
                                {{ $data['user']->first_name }} {{ $data['user']->last_name }}
                            </a>
                            <div class="bday-meta">
                                {{ $data['last_birthday']->format('j F Y') }} &middot;
                                {{ __('Turned :age', ['age' => $data['turned_age']]) }}
                            </div>
                        </div>
                    </div>

                    @include('friends.quick-birthday-composer', ['id' => 'recent-'.$i, 'friend' => $data['user'], 'alwaysOpen' => true])
                </div>
            @endforeach
        @endif

        {{-- ================= ALL OTHER FRIEND BIRTHDAYS, BY MONTH {{ route('profile.show', $data['user']) }} ================= --}}
        @if ($monthGroups->isNotEmpty())
            <div class="bday-section-title mt-4">{{ __('Friend birthdays by month') }}</div>

            @foreach ($monthGroups as $group)
                <div class="month-group">
                    <div class="month-group-title">{{ $group['label'] }}</div>
                    {{-- <div class="month-group-subtitle">
                        @php
                            $names = $group['friends']->take(2)->map(fn ($d) => $d['user']->first_name);
                            $remainingCount = $group['friends']->count() - $names->count();
                        @endphp

                        {{ $names->implode(', ') }}
                        @if ($remainingCount > 0)
                            {{ __('and :n others', ['n' => $remainingCount]) }}
                        @endif
                    </div> --}}

                    <div class="month-grid">
                        @foreach ($group['friends'] as $data)
                            <a href="" class="month-grid-link" title="{{ $data['user']->first_name.' '.$data['user']->last_name.' — '.$data['dob']->format('j F') }}">
                                @if ($data['user']->avatar)
                                    <img src="{{ asset('storage/'.$data['user']->avatar) }}" class="month-grid-avatar" alt="{{ $data['user']->first_name }}">
                                @else
                                    <div class="month-grid-avatar-fallback">
                                        {{ strtoupper(mb_substr($data['user']->first_name, 0, 1)) }}
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

    </div>
</div>

<script>
    document.querySelectorAll('.toggle-composer').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.getElementById('composer-' + this.dataset.target);
            if (target) target.classList.toggle('active');
        });
    });

    document.querySelectorAll('.quick-msg-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const composer = this.closest('.quick-messages').nextElementSibling;
            const textarea = composer ? composer.querySelector('textarea') : null;
            if (textarea) {
                textarea.value = this.dataset.message;
                composer.classList.add('active');
                textarea.focus();
            }
        });
    });
</script>
@endsection