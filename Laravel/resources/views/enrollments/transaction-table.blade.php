<div class="table-wrap">
    <table>
        <thead><tr><th>Date</th><th>Student</th><th>Course</th><th>Action</th><th>Slot change</th></tr></thead>
        <tbody>
        @forelse($transactions as $transaction)
            <tr>
                <td>{{ $transaction->created_at->format('M j, Y · g:i a') }}</td>
                <td>{{ $transaction->user->name }}</td>
                <td><a class="text-link" href="{{ route('courses.show', $transaction->course) }}">{{ $transaction->course->name }}</a></td>
                <td><span class="type-badge type-{{ $transaction->type }}">{{ ucfirst($transaction->type) }}</span></td>
                <td class="{{ $transaction->quantity < 0 ? 'negative' : 'positive' }}">{{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="table-empty">No enrollment activity yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
