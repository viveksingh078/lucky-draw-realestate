@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>Membership Plans</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">Manage membership plans for user registration</p>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <div class="mb-3">
                        <a href="{{ route('membership-plans.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New Plan
                        </a>
                    </div>

                    <div class="table-wrapper">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Sort Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plans as $plan)
                                    <tr>
                                        <td><strong>{{ $plan->name }}</strong></td>
                                        <td>₹{{ number_format($plan->price, 2) }}</td>
                                        <td>{{ round($plan->duration_days / 30) }} Months</td>
                                        <td>
                                            @if($plan->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $plan->sort_order }}</td>
                                        <td>
                                            <a href="{{ route('membership-plans.edit', $plan->id) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="{{ $plan->id }}">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No membership plans found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (confirm('Are you sure you want to delete this plan?')) {
                $.ajax({
                    url: '{{ url('admin/real-estate/membership-plans') }}/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.error) {
                            alert(response.message);
                        } else {
                            alert('Plan deleted successfully!');
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        alert('Error deleting plan');
                    }
                });
            }
        });
    </script>
@endsection
