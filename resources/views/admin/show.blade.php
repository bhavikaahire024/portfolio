@extends('layout')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4>Admin: {{ $poll->question }}</h4>
                <p class="text-muted">Release an IP to remove its vote. Released votes remain in history.</p>
            </div>
            <a href="{{ route('polls.show', $poll->id) }}" class="btn btn-outline-primary btn-sm">Back to Poll</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Current Votes</h5>
                <ul class="list-group" id="vote-list">
                    @forelse($votes as $vote)
                        <li class="list-group-item d-flex justify-content-between align-items-center" data-ip="{{ $vote->ip_address }}">
                            <div>
                                <div><strong>{{ $vote->ip_address }}</strong></div>
                                <small class="text-muted">{{ $vote->option_text }} · {{ $vote->created_at }}</small>
                            </div>
                            <button class="btn btn-sm btn-danger release-btn">Release</button>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No votes yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Vote History</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Previous Vote</th>
                                <th>Released</th>
                                <th>New Vote</th>
                            </tr>
                        </thead>
                        <tbody id="history-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const pollId = {{ $poll->id }};

    const loadHistory = () => {
        $.get(`/admin/polls/${pollId}/history`, function (response) {
            if (response.status !== 'ok') {
                return;
            }
            const body = $('#history-body');
            body.empty();
            if (!response.history.length) {
                body.append('<tr><td colspan="4" class="text-muted">No history yet.</td></tr>');
                return;
            }
            response.history.forEach((item) => {
                body.append(`
                    <tr>
                        <td>${item.ip_address}</td>
                        <td>${item.previous_option ?? 'N/A'}<br><small class="text-muted">${item.previous_voted_at ?? ''}</small></td>
                        <td>${item.released_at ?? ''}</td>
                        <td>${item.new_option ?? 'Pending'}<br><small class="text-muted">${item.new_voted_at ?? ''}</small></td>
                    </tr>
                `);
            });
        });
    };

    loadHistory();
    setInterval(loadHistory, 1000);

    $('#vote-list').on('click', '.release-btn', function () {
        const item = $(this).closest('li');
        const ipAddress = item.data('ip');

        $.post(`/admin/polls/${pollId}/release`, { ip_address: ipAddress })
            .done(function () {
                item.remove();
                if (!$('#vote-list li').length) {
                    $('#vote-list').html('<li class="list-group-item text-muted">No votes yet.</li>');
                }
                loadHistory();
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON?.message || 'Unable to release IP.');
            });
    });
</script>
@endpush
