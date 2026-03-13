@extends(Theme::getThemeNamespace() . '::views.template')

@section('content')
    <div class="container my-5">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">🎯 My Reward Draws</h2>
                        <p class="text-muted">Track your participations and winnings</p>
                    </div>
                    <a href="{{ route('public.lucky-draws.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Join New Draw
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-ticket-alt fa-2x text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $stats['total_joined'] }}</h4>
                        <small class="text-muted">Total Draws Joined</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-trophy fa-2x text-warning"></i>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $stats['total_won'] }}</h4>
                        <small class="text-muted">Draws Won</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-coins fa-2x text-success"></i>
                        </div>
                        <h4 class="fw-bold mb-1">₹{{ number_format($stats['available_credits'], 0) }}</h4>
                        <small class="text-muted">Available Credits</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-play-circle fa-2x text-info"></i>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $stats['active_participations'] }}</h4>
                        <small class="text-muted">Active Draws</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Won Draws -->
        @if($wonDraws->count() > 0)
            <div class="row mb-5">
                <div class="col-12">
                    <h4 class="fw-bold mb-3">🏆 Congratulations! You Won</h4>
                    <div class="row">
                        @foreach($wonDraws as $won)
                            <div class="col-lg-6 mb-3">
                                <div class="card border-success shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-trophy fa-3x text-warning"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="fw-bold text-success mb-1">{{ $won->draw->name }}</h5>
                                                <p class="mb-1">{{ $won->draw->property->name ?? 'Premium Property' }}</p>
                                                <small class="text-muted">Won on {{ $won->draw->draw_date->format('M d, Y') }}</small>
                                            </div>
                                            <div class="text-end">
                                                <i class="fas fa-trophy fa-3x text-warning"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Active Participations -->
        @if($activeParticipations->count() > 0)
            <div class="row mb-5">
                <div class="col-12">
                    <h4 class="fw-bold mb-3">⏳ Active Participations</h4>
                    <div class="row">
                        @foreach($activeParticipations as $participation)
                            <div class="col-lg-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <!-- Property Image -->
                                    @if($participation->draw->property && $participation->draw->property->image)
                                        <img src="{{ RvMedia::getImageUrl($participation->draw->property->image) }}" 
                                             class="card-img-top" style="height: 200px; object-fit: cover;" 
                                             alt="{{ $participation->draw->property->name }}">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <i class="fas fa-home fa-3x text-muted"></i>
                                        </div>
                                    @endif
                                    
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="fw-bold mb-1">{{ $participation->draw->name }}</h5>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt"></i> 
                                                    {{ $participation->draw->property->location ?? 'Premium Location' }}
                                                </small>
                                            </div>
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                        
                                        <!-- Prize and Timer - Side by Side with Gradient -->
                                        <div class="row g-3 mb-3">
                                            <div class="col-6">
                                                <div class="p-4 rounded h-100 text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <i class="fas fa-trophy fa-3x text-white mb-2"></i>
                                                    <h6 class="fw-bold text-white mb-0">{{ $participation->draw->property->name ?? 'Premium Property' }}</h6>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                @php
                                                    $drawDate = $participation->draw->draw_date;
                                                    $now = now();
                                                    $daysLeft = $now->diffInDays($drawDate, false);
                                                    $hoursLeft = $now->diffInHours($drawDate, false) % 24;
                                                @endphp
                                                <div class="p-4 rounded h-100 text-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                                    <i class="fas fa-clock fa-3x text-white mb-2"></i>
                                                    @if($daysLeft > 0)
                                                        <h4 class="fw-bold text-white mb-0">{{ $daysLeft }}</h4>
                                                        <small class="text-white">{{ $daysLeft == 1 ? 'Day Left' : 'Days Left' }}</small>
                                                    @elseif($hoursLeft > 0)
                                                        <h4 class="fw-bold text-white mb-0">{{ $hoursLeft }}</h4>
                                                        <small class="text-white">{{ $hoursLeft == 1 ? 'Hour Left' : 'Hours Left' }}</small>
                                                    @else
                                                        <h5 class="fw-bold text-white mb-0">Drawing Soon!</h5>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> {{ $drawDate->format('M d, Y h:i A') }}
                                            </small>
                                        </div>

                                        <div class="mt-3">
                                            <a href="{{ route('public.lucky-draws.show', $participation->draw->id) }}" 
                                               class="btn btn-outline-primary btn-sm w-100">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Completed Participations -->
        @if($completedParticipations->count() > 0)
            <div class="row mb-5">
                <div class="col-12">
                    <h4 class="fw-bold mb-3">📋 Recent History</h4>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Draw Name</th>
                                            <th>Property</th>
                                            <th>Result</th>
                                            <th>Credit Given</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($completedParticipations as $participation)
                                            <tr>
                                                <td>
                                                    <strong>{{ $participation->draw->name }}</strong>
                                                </td>
                                                <td>{{ $participation->draw->property->name ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        // Check if user won by comparing with draw's winner_id
                                                        $isWinner = ($participation->draw->winner_type === 'real' && 
                                                                    $participation->draw->winner_id == auth('account')->id()) ||
                                                                    ($participation->is_winner === true || $participation->is_winner === 1);
                                                    @endphp
                                                    @if($isWinner)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-trophy"></i> WON
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-times"></i> Lost
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!$isWinner && $participation->credit_given > 0)
                                                        <span class="text-success">₹{{ number_format($participation->credit_given, 0) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $participation->draw->draw_date->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if($activeParticipations->count() == 0 && $completedParticipations->count() == 0 && $wonDraws->count() == 0)
            <div class="row">
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-ticket-alt fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Draw Participations Yet</h4>
                        <p class="text-muted mb-4">Join your first reward draw and get a chance to win amazing properties!</p>
                        <a href="{{ route('public.lucky-draws.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Browse Active Draws
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Credits Info -->
        @if($stats['available_credits'] > 0)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <h5 class="mb-1">💰 You have ₹{{ number_format($stats['available_credits'], 0) }} in credits!</h5>
                                <p class="mb-0">Use these credits as discount when purchasing properties from our portal.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('footer')
<style>
.card {
    transition: all 0.3s ease-in-out;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
.progress {
    border-radius: 10px;
}
.progress-bar {
    border-radius: 10px;
}
/* Gradient boxes hover effect */
.row.g-3 > div > div {
    transition: transform 0.2s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.row.g-3 > div > div:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}
</style>
@endpush