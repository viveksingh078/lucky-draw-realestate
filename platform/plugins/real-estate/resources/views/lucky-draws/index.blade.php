@extends('core/base::layouts.master')

@section('content')
<div class="max-width-1200">
    <div class="flexbox-annotated-section">

        <div class="flexbox-annotated-section-annotation">
            <div class="annotated-section-title pd-all-20">
                <h2>Reward Draws Management</h2>
            </div>
            <div class="annotated-section-description pd-all-20 p-none-t">
                <p class="color-note">Manage reward draws for properties</p>
            </div>
        </div>

        <div class="flexbox-annotated-section-content">
            <div class="wrapper-content pd-all-20">

                {{-- Create Button --}}
                <div class="mb-3">
                    <a href="{{ route('lucky-draws.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Create Reward Draw
                    </a>
                </div>

                {{-- Table --}}
                <div class="table-wrapper">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Draw Name</th>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Entry Fee</th>
                                <th>Property Value</th>
                                <th>Participants</th>
                                <th>Status</th>
                                <th>Draw Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse($draws as $index => $draw)
                            <tr>
                                <td>
                                    {{ $index + 1 + ($draws->currentPage() - 1) * $draws->perPage() }}
                                </td>

                                <td>
                                    <strong>{{ $draw->name }}</strong><br>
                                    <small class="text-muted">
                                        {{ Str::limit($draw->description, 40) }}
                                    </small>
                                </td>

                                <td>
                                    <span class="text-primary">
                                        {{ $draw->property->name ?? 'N/A' }}
                                    </span><br>
                                    <small class="text-muted">
                                        {{ Str::limit($draw->property->location ?? '', 30) }}
                                    </small>
                                </td>

                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst($draw->draw_type) }}
                                    </span>
                                </td>

                                <td>₹{{ number_format($draw->entry_fee, 2) }}</td>
                                <td>₹{{ number_format($draw->property_value, 2) }}</td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ $draw->participants->where('payment_status', 'paid')->count() }}
                                    </span>
                                </td>

                                <td>
                                    @if($draw->status === 'upcoming')
                                        <span class="badge bg-secondary">Upcoming</span>
                                    @elseif($draw->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($draw->status === 'completed')
                                        <span class="badge bg-info">Completed</span>
                                    @else
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $draw->draw_date->format('M d, Y') }}
                                </td>

                                {{-- Actions Dropdown --}}
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            Actions
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end">

                                            {{-- View --}}
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('lucky-draws.show', $draw->id) }}">
                                                    <i class="fa fa-eye text-info me-2"></i> View Details
                                                </a>
                                            </li>

                                            {{-- Edit --}}
                                            @if($draw->status !== 'completed')
                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('lucky-draws.edit', $draw->id) }}">
                                                        <i class="fa fa-edit text-warning me-2"></i> Edit
                                                    </a>
                                                </li>
                                            @endif

                                            {{-- Activate --}}
                                            @if($draw->status === 'upcoming')
                                                <li>
                                                    <a href="#"
                                                       class="dropdown-item btn-activate"
                                                       data-id="{{ $draw->id }}">
                                                        <i class="fa fa-play text-success me-2"></i> Activate
                                                    </a>
                                                </li>
                                            @endif

                                            {{-- Execute --}}
                                            @if($draw->status === 'active' && $draw->draw_date <= now())
                                                <li>
                                                    <a href="#"
                                                       class="dropdown-item btn-execute"
                                                       data-id="{{ $draw->id }}">
                                                        <i class="fa fa-trophy text-primary me-2"></i> Execute Draw
                                                    </a>
                                                </li>
                                            @endif

                                            {{-- Delete --}}
                                            @if($draw->participants->isEmpty())
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a href="#"
                                                       class="dropdown-item text-danger btn-delete"
                                                       data-id="{{ $draw->id }}">
                                                        <i class="fa fa-trash me-2"></i> Delete
                                                    </a>
                                                </li>
                                            @endif

                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    No reward draws found
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($draws->hasPages())
                    <div class="mt-3">
                        {{ $draws->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- JS --}}
<script>
$(document).on('click', '.btn-activate', function (e) {
    e.preventDefault();
    let id = $(this).data('id');

    if (confirm('Are you sure you want to activate this draw?')) {
        $.post(`{{ url('admin/real-estate/lucky-draws') }}/${id}/activate`, {
            _token: '{{ csrf_token() }}'
        }).done(() => {
            alert('Draw activated successfully!');
            location.reload();
        }).fail(() => {
            alert('Error activating draw');
        });
    }
});

$(document).on('click', '.btn-execute', function (e) {
    e.preventDefault();
    let id = $(this).data('id');

    if (confirm('Execute this draw? Winner selection cannot be undone.')) {
        $.post(`{{ url('admin/real-estate/lucky-draws') }}/${id}/execute`, {
            _token: '{{ csrf_token() }}'
        }).done(res => {
            alert('Draw executed successfully! Winner: ' + res.message);
            location.reload();
        }).fail(() => {
            alert('Error executing draw');
        });
    }
});

$(document).on('click', '.btn-delete', function (e) {
    e.preventDefault();
    let id = $(this).data('id');

    if (confirm('Are you sure you want to delete this draw?')) {
        $.ajax({
            url: `{{ url('admin/real-estate/lucky-draws') }}/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: () => {
                alert('Draw deleted successfully!');
                location.reload();
            },
            error: () => {
                alert('Error deleting draw');
            }
        });
    }
});
</script>
@endsection
