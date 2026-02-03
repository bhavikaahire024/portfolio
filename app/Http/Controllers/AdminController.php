<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function show($pollId)
    {
        $poll = DB::table('polls')->where('id', $pollId)->first();
        if (!$poll) {
            abort(404);
        }

        $votes = DB::table('votes')
            ->join('poll_options', 'votes.option_id', '=', 'poll_options.id')
            ->select('votes.ip_address', 'votes.created_at', 'poll_options.option_text')
            ->where('votes.poll_id', $pollId)
            ->orderBy('votes.created_at', 'desc')
            ->get();

        return view('admin.show', [
            'poll' => $poll,
            'votes' => $votes,
        ]);
    }

    public function release(Request $request, $pollId)
    {
        $data = $request->validate([
            'ip_address' => ['required', 'ip'],
        ]);

        $vote = DB::table('votes')
            ->where('poll_id', $pollId)
            ->where('ip_address', $data['ip_address'])
            ->first();

        if (!$vote) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vote not found for this IP.',
            ], 404);
        }

        DB::transaction(function () use ($vote, $pollId, $data) {
            $now = Carbon::now();

            DB::table('vote_histories')->insert([
                'poll_id' => $pollId,
                'ip_address' => $data['ip_address'],
                'previous_option_id' => $vote->option_id,
                'previous_voted_at' => $vote->created_at,
                'released_at' => $now,
                'new_option_id' => null,
                'new_voted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('votes')
                ->where('poll_id', $pollId)
                ->where('ip_address', $data['ip_address'])
                ->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'IP released and vote removed.',
        ]);
    }

    public function history($pollId)
    {
        $history = DB::table('vote_histories')
            ->leftJoin('poll_options as prev_option', 'vote_histories.previous_option_id', '=', 'prev_option.id')
            ->leftJoin('poll_options as new_option', 'vote_histories.new_option_id', '=', 'new_option.id')
            ->where('vote_histories.poll_id', $pollId)
            ->select(
                'vote_histories.ip_address',
                'prev_option.option_text as previous_option',
                'vote_histories.previous_voted_at',
                'vote_histories.released_at',
                'new_option.option_text as new_option',
                'vote_histories.new_voted_at'
            )
            ->orderBy('vote_histories.released_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'ok',
            'history' => $history,
        ]);
    }
}
