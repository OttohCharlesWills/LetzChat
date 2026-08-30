@extends('layouts.adminapp')

@section('content')
<div class="container">

    @if (session('status'))
        @include('friends.flash')
    @endif

    <h4 class="mb-3">{{ __('Banned Words') }} <span class="text-muted">({{ $words->total() }})</span></h4>

    <div class="pc-card mb-3">
        <form method="POST" action="{{ route('admin.banned-words.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label small mb-1">{{ __('Word or phrase') }}</label>
                <input type="text" name="word" class="form-control" required maxlength="255">
            </div>

            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Severity') }}</label>
                <select name="severity" class="form-select" required>
                    <option value="flag">{{ __('Flag (stays visible, sent for review)') }}</option>
                    <option value="block">{{ __('Block (rejected outright)') }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Category') }} <span class="text-muted">({{ __('optional') }})</span></label>
                <input type="text" name="category" class="form-control" maxlength="100" placeholder="{{ __('e.g. hate, violence, spam') }}">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">{{ __('Add word') }}</button>
            </div>
        </form>
    </div>

    <div class="pc-card">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ __('Word') }}</th>
                    <th>{{ __('Severity') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($words as $word)
                    <tr>
                        <td>{{ $word->word }}</td>
                        <td>
                            <span class="badge {{ $word->severity === 'block' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ ucfirst($word->severity) }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $word->category ?? '—' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.banned-words.destroy', $word) }}"
                                  onsubmit="return confirm('{{ __('Remove this word?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Remove') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted text-center py-4">{{ __('No banned words yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($words->hasPages())
        <div class="mt-3">
            {{ $words->links() }}
        </div>
    @endif

</div>
@endsection