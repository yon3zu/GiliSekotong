<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index()
    {
        return view('admin.reviews.index', [
            'reviews' => Review::query()->with(['user', 'boat', 'booking'])->latest()->paginate(20),
            'average' => Review::query()->visible()->avg('rating'),
        ]);
    }

    /** Ulasan tidak bisa diubah staf, hanya disembunyikan bila melanggar (kata kasar, spam, dan sejenisnya). */
    public function toggle(Review $review)
    {
        $review->update(['is_visible' => ! $review->is_visible]);

        return back()->with('success', $review->is_visible ? 'Ulasan ditampilkan.' : 'Ulasan disembunyikan dari halaman publik.');
    }
}
