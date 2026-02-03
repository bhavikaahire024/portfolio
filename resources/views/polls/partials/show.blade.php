<div class="card-body">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4>{{ $poll->question }}</h4>
            <p class="text-muted">Vote once per IP address.</p>
        </div>
        <a href="{{ route('admin.polls.show', $poll->id) }}" class="btn btn-outline-secondary btn-sm">Admin View</a>
    </div>

    <form id="vote-form" class="mt-3">
        @foreach($options as $option)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="option_id" value="{{ $option->id }}" id="option-{{ $option->id }}">
                <label class="form-check-label" for="option-{{ $option->id }}">
                    {{ $option->option_text }}
                </label>
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary mt-3">Submit Vote</button>
    </form>

    <div id="vote-message" class="mt-3"></div>

    <hr>

    <h5>Live Results</h5>
    <ul class="list-group" id="result-list"></ul>
</div>

<script>
    const pollId = {{ $poll->id }};

    const renderResults = (options) => {
        const list = $('#result-list');
        list.empty();
        options.forEach((option) => {
            list.append(`
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>${option.option_text}</span>
                    <span class="badge bg-primary rounded-pill">${option.vote_count}</span>
                </li>
            `);
        });
    };

    const fetchResults = () => {
        $.get(`/polls/${pollId}/results`, function (response) {
            if (response.status === 'ok') {
                renderResults(response.options);
            }
        });
    };

    if (window.pollResultsInterval) {
        clearInterval(window.pollResultsInterval);
    }

    fetchResults();
    window.pollResultsInterval = setInterval(fetchResults, 1000);

    $('#vote-form').off('submit').on('submit', function (event) {
        event.preventDefault();
        const optionId = $('input[name="option_id"]:checked').val();
        if (!optionId) {
            $('#vote-message').html('<div class="alert alert-warning">Select an option first.</div>');
            return;
        }

        $.post(`/polls/${pollId}/vote`, { option_id: optionId })
            .done(function (response) {
                $('#vote-message').html(`<div class="alert alert-success">${response.message}</div>`);
                fetchResults();
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Unable to vote.';
                $('#vote-message').html(`<div class="alert alert-danger">${message}</div>`);
            });
    });
</script>
