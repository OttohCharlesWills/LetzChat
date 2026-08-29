<?php

namespace App\Http\Controllers;

use App\Models\Sticker;
use Illuminate\Http\Request;

class StickerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $stickers = Sticker::active()
            ->orderBy('pack')
            ->orderBy('sort_order')
            ->get(['uuid', 'pack', 'name', 'image_path']);

        return response()->json(['stickers' => $stickers->groupBy('pack')]);
    }
}