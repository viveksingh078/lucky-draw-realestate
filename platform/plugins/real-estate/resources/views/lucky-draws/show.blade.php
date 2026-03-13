@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>{{ $draw->name }}</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">View reward draw details and participants</p>
                    <a href="{{ route('lucky-draws.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    
                    <!-- Draw Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>Draw Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Draw Name:</strong> {{ $draw->name }}</p>
                                    <p><strong>Draw Type:</strong> <span class="badge bg-info">{{ ucfirst($draw->draw_type) }}</span></p>
                                    <p><strong>Status:</strong> 
                                        @if($draw->status === 'upcoming')
                                            <span class="badge bg-secondary">Upcoming</span>
                                        @elseif($draw->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($draw->status === 'completed')
                                            <span class="badge bg-info">Completed</span>
                                        @else
                                            <span class="badge bg-danger">Cancelled</span>
                                        @endif
                                    </p>
                                    <p><strong>Description:</strong> {{ $draw->description ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Property:</strong> {{ $draw->property->name ?? 'N/A' }}</p>
                                    <p><strong>Property Value:</strong> ₹{{ number_format($draw->property_value, 2) }}</p>
                                    <p><strong>Entry Fee:</strong> ₹{{ number_format($draw->entry_fee, 2) }}</p>
                                    <p><strong>Start Date:</strong> {{ $draw->start_date->format('M d, Y H:i') }}</p>
                                    <p><strong>End Date:</strong> {{ $draw->end_date->format('M d, Y H:i') }}</p>
                                    <p><strong>Draw Date:</strong> {{ $draw->draw_date->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>Statistics</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-primary">{{ $stats['total_participants'] }}</h3>
                                        <p>Total Participants</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-success">₹{{ number_format($stats['total_pool'], 2) }}</h3>
                                        <p>Total Pool</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-warning">{{ $stats['pending_payments'] }}</h3>
                                        <p>Pending Payments</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="{{ $stats['is_profitable'] ? 'text-success' : 'text-danger' }}">
                                            ₹{{ number_format($stats['profit_loss'], 2) }}
                                        </h3>
                                        <p>{{ $stats['is_profitable'] ? 'Profit' : 'Loss' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Participants List -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Participants ({{ $stats['total_participants'] }})</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-wrapper">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Entry Fee</th>
                                            <th>Payment Status</th>
                                            <th>Joined At</th>
                                            <th>Winner</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($draw->participants as $index => $participant)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $participant->account->name ?? 'N/A' }}</td>
                                                <td>{{ $participant->account->email ?? 'N/A' }}</td>
                                                <td>{{ $participant->account->phone ?? 'N/A' }}</td>
                                                <td>₹{{ number_format($participant->entry_fee_paid, 2) }}</td>
                                                <td>
                                                    @if($participant->payment_status === 'paid')
                                                        <span class="badge bg-success">Paid</span>
                                                    @elseif($participant->payment_status === 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @else
                                                        <span class="badge bg-danger">Refunded</span>
                                                    @endif
                                                </td>
                                                <td>{{ $participant->joined_at->format('M d, Y H:i') }}</td>
                                                <td>
                                                    @if($participant->is_winner)
                                                        <span class="badge bg-success"><i class="fa fa-trophy"></i> Winner</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No participants yet</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-3">
                        @if($draw->status !== 'completed')
                            <a href="{{ route('lucky-draws.edit', $draw->id) }}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit Draw
                            </a>
                        @endif
                        
                        @if($draw->status === 'upcoming')
                            <button class="btn btn-success btn-activate" data-id="{{ $draw->id }}">
                                <i class="fa fa-play"></i> Activate Draw
                            </button>
                        @endif
                        
                        @if($draw->status === 'active')
                            <a href="{{ route('lucky-draws.select-winner', $draw->id) }}" class="btn btn-success btn-lg">
                                <i class="fa fa-trophy"></i> Select Winner Manually
                            </a>
                        @endif
                        
                        @if($draw->status === 'active' && $draw->draw_date <= now())
                            <button class="btn btn-primary btn-execute" data-id="{{ $draw->id }}">
                                <i class="fa fa-random"></i> Auto Execute Draw
                            </button>
                        @endif
                        
                        <a href="{{ route('lucky-draws.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).on('click', '.btn-activate', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (confirm('Are you sure you want to activate this draw?')) {
                $.ajax({
                    url: '{{ url('admin/real-estate/lucky-draws') }}/' + id + '/activate',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        alert(response.error ? response.message : 'Draw activated successfully!');
                        location.reload();
                    }
                });
            }
        });

        $(document).on('click', '.btn-execute', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (confirm('Execute this draw? This will select a winner!')) {
                $.ajax({
                    url: '{{ url('admin/real-estate/lucky-draws') }}/' + id + '/execute',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        alert(response.error ? response.message : 'Draw executed! Winner selected.');
                        location.reload();
                    }
                });
            }
        });
    </script>
@endsection
