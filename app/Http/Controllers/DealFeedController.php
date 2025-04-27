<?php
namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealFeedController extends Controller
{
    public function store(Request $request, $dealId)
    {
        $request->validate([
            'content' => 'required|string|max:1990',
        ]);

        $deal = Deal::findOrFail($dealId);
        $feed = DealFeed::create([
            'deal_id' => $dealId,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        // Проверка статуса сделки - если "Проект завершен", нужно уведомить об оценках
        $needRating = $deal->status === 'Проект завершен';

        return response()->json([
            'user_name'  => $feed->user->name,
            'content'    => $feed->content,
            'date'       => $feed->created_at->format('d.m.Y H:i'),
            'avatar_url' => $feed->user->avatar_url,
            'need_rating' => $needRating,
            'deal_id' => $deal->id
        ]);
    }
}
