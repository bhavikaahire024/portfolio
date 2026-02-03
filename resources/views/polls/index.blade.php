@extends('layout')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Active Polls</h5>
                <ul class="list-group" id="poll-list">
                    @forelse($polls as $poll)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $poll->question }}</span>
                            <button class="btn btn-sm btn-primary" data-poll-id="{{ $poll->id }}">Open</button>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No active polls.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div id="poll-view" class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted">Select a poll to start voting.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const loadPoll = (pollId) => {
        $('#poll-view').addClass('opacity-50');
        $.get(`/polls/${pollId}`, function (html) {
            $('#poll-view').html(html).removeClass('opacity-50');
        }).fail(function () {
            $('#poll-view').html('<div class="card-body"><p class="text-danger">Unable to load poll.</p></div>');
        });
    };

    $('#poll-list').on('click', 'button', function () {
        const pollId = $(this).data('poll-id');
        loadPoll(pollId);
    });
</script>
@endpush
