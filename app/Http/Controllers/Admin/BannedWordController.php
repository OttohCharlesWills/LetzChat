<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedWord;
use Illuminate\Http\Request;

class BannedWordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()->is_admin, 403);
            return $next($request);
        });
    }

    public function index()
    {
        $words = BannedWord::orderBy('severity')->orderBy('word')->paginate(50);

        return view('admin.banned-words.index', compact('words'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'word'     => ['required', 'string', 'max:255', 'unique:banned_words,word'],
            'severity' => ['required', 'in:flag,block'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        BannedWord::create([
            ...$validated,
            'added_by' => $request->user()->id,
        ]);

        return back()->with('status', __('Word added.'));
    }

    public function destroy(BannedWord $bannedWord)
    {
        $bannedWord->delete();

        return back()->with('status', __('Word removed.'));
    }
}