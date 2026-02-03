<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PollController extends Controller
{
    public function index()
    {
        $polls = DB::table('polls')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('polls.index', [
            'polls' => $polls,
        ]);
    }

    public function show(Request $request, $pollId)
    {
        $poll = DB::table('polls')
            ->where('id', $pollId)
            ->where('status', 'active')
            ->first();

        if (!$poll) {
            abort(404);
        }

        $options = DB::table('poll_options')
            ->where('poll_id', $pollId)
            ->orderBy('id')
            ->get();

        $view = $request->ajax() ? 'polls.partials.show' : 'polls.show';

        return view($view, [
            'poll' => $poll,
            'options' => $options,
        ]);
    }

    public function vote(Request $request, $pollId)
    {
        $data = $request->validate([
            'option_id' => ['required', 'integer'],
        ]);

        $poll = DB::table('polls')
            ->where('id', $pollId)
            ->where('status', 'active')
            ->first();

        if (!$poll) {
            return response()->json([
                'status' => 'error',
                'message' => 'Poll is not available.',
            ], 404);
        }

        $option = DB::table('poll_options')
            ->where('id', $data['option_id'])
            ->where('poll_id', $pollId)
            ->first();

        if (!$option) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid option selection.',
            ], 422);
        }

        $ipAddress = $request->ip();

        $existingVote = DB::table('votes')
            ->where('poll_id', $pollId)
            ->where('ip_address', $ipAddress)
            ->first();

        if ($existingVote) {
            return response()->json([
                'status' => 'blocked',
                'message' => 'This IP has already voted on this poll.',
            ], 409);
        }

        DB::transaction(function () use ($pollId, $data, $ipAddress) {
            $now = Carbon::now();

            DB::table('votes')->insert([
                'poll_id' => $pollId,
                'option_id' => $data['option_id'],
                'ip_address' => $ipAddress,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $openHistory = DB::table('vote_histories')
                ->where('poll_id', $pollId)
                ->where('ip_address', $ipAddress)
                ->whereNull('new_option_id')
                ->orderByDesc('id')
                ->first();

            if ($openHistory) {
                DB::table('vote_histories')
                    ->where('id', $openHistory->id)
                    ->update([
                        'new_option_id' => $data['option_id'],
                        'new_voted_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Vote recorded.',
        ]);
    }

    public function results($pollId)
    {
        $options = DB::table('poll_options')
            ->leftJoin('votes', function ($join) {
                $join->on('poll_options.id', '=', 'votes.option_id');
            })
            ->where('poll_options.poll_id', $pollId)
            ->select('poll_options.id', 'poll_options.option_text', DB::raw('COUNT(votes.id) as vote_count'))
            ->groupBy('poll_options.id', 'poll_options.option_text')
            ->orderBy('poll_options.id')
            ->get();

        return response()->json([
            'status' => 'ok',
            'options' => $options,
        ]);
    }
}
