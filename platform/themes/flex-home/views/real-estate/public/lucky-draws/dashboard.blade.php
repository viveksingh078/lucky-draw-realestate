<div class="container my-5">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">🎯 My Reward Draws Dashboard</h2>
                        <p class="text-muted">Track your participations and activity</p>
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
                        <h4 class="fw-bold mb-1">{{ auth('account')->user()->draws_remaining }}</h4>
                        <small class="text-muted">Draws Remaining</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
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
                                                <p class="mb-1">{{ Str::title($won->draw->property->name ?? 'Premium Property') }}</p>
                                                <small class="text-muted">Won on {{ $won->draw->draw_date->format('M d, Y') }}</small>
                                            </div>
                                            <div class="text-end">
                                                <h5 class="fw-bold text-success">₹{{ number_format($won->draw->property_value/100000, 1) }}L</h5>
                                                <small class="text-muted">Prize Value</small>
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
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="fw-bold mb-1">{{ $participation->draw->name }}</h5>
                                                <p class="text-muted mb-1">{{ Str::title($participation->draw->property->name ?? 'Premium Property') }}</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> 
                                                    Draw on {{ $participation->draw->draw_date->format('M d, Y H:i') }}
                                                </small>
                                            </div>
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                        
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted">Entry Fee</small>
                                                <h6 class="fw-bold">₹{{ number_format($participation->entry_fee_paid, 0) }}</h6>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">Prize Value</small>
                                                <h6 class="fw-bold">₹{{ number_format($participation->draw->property_value/100000, 1) }}L</h6>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">Participants</small>
                                                <h6 class="fw-bold">{{ $participation->draw->participants->where('payment_status', 'paid')->count() }}</h6>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span>Time Left</span>
                                                <span class="fw-bold">{{ $participation->draw->end_date->diffForHumans() }}</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                @php
                                                    $totalTime = $participation->draw->start_date->diffInHours($participation->draw->end_date);
                                                    $timeLeft = now()->diffInHours($participation->draw->end_date, false);
                                                    $progressPercent = $totalTime > 0 ? max(0, min(100, (($totalTime - $timeLeft) / $totalTime) * 100)) : 0;
                                                @endphp
                                                <div class="progress-bar bg-info" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <a href="{{ route('public.lucky-draws.show', $participation->draw->id) }}" 
                                               class="btn btn-outline-primary btn-sm">
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

        <!-- Activity Logs -->
        <div class="row mb-5">
            <div class="col-12">
                <h4 class="fw-bold mb-3">📋 Activity Logs</h4>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        @php
                            $activities = [];
                            
                            // Add membership activation
                            if(auth('account')->user()->membership_start_date) {
                                $activities[] = [
                                    'type' => 'membership',
                                    'icon' => 'fa-crown',
                                    'color' => 'success',
                                    'title' => 'Membership Activated',
                                    'description' => auth('account')->user()->membershipPlan->name . ' Plan - ' . auth('account')->user()->membershipPlan->draws_allowed . ' draws credited',
                                    'date' => auth('account')->user()->membership_start_date,
                                ];
                            }
                            
                            // Add draw participations
                            foreach($activeParticipations as $participation) {
                                $activities[] = [
                                    'type' => 'join',
                                    'icon' => 'fa-ticket-alt',
                                    'color' => 'primary',
                                    'title' => 'Joined Draw',
                                    'description' => $participation->draw->name . ' - 1 credit used',
                                    'date' => $participation->joined_at ?? $participation->created_at,
                                ];
                            }
                            
                            foreach($completedParticipations as $participation) {
                                // Check if user won by comparing with draw's winner_id
                                $currentUserId = auth('account')->id();
                                $drawWinnerId = $participation->draw->winner_id;
                                $drawWinnerType = $participation->draw->winner_type;
                                $participantIsWinner = $participation->is_winner;
                                
                                $isWinner = false;
                                
                                // If winner is a real user and winner_id matches current user
                                if ($drawWinnerType === 'real' && $drawWinnerId == $currentUserId) {
                                    $isWinner = true;
                                }
                                
                                // Also check participant's is_winner field as fallback
                                if ($participantIsWinner === true || $participantIsWinner === 1 || $participantIsWinner === '1') {
                                    $isWinner = true;
                                }
                                
                                if($isWinner) {
                                    $propertyName = $participation->draw->property ? Str::title($participation->draw->property->name) : 'Premium Property';
                                    $propertyUrl = $participation->draw->property && $participation->draw->property->slug 
                                        ? route('public.properties') . '/' . $participation->draw->property->slug 
                                        : '#';
                                    
                                    $activities[] = [
                                        'type' => 'won',
                                        'icon' => 'fa-trophy',
                                        'color' => 'success',
                                        'title' => '🎉 Won Draw!',
                                        'description' => $participation->draw->name . ' - Congratulations! You won ' . $propertyName,
                                        'property_url' => $propertyUrl,
                                        'property_name' => $propertyName,
                                        'date' => $participation->draw->draw_date,
                                    ];
                                } else {
                                    $activities[] = [
                                        'type' => 'lost',
                                        'icon' => 'fa-times-circle',
                                        'color' => 'secondary',
                                        'title' => 'Draw Completed',
                                        'description' => $participation->draw->name . ' - Better luck next time!',
                                        'date' => $participation->draw->draw_date,
                                    ];
                                }
                            }
                            
                            // Sort by date descending
                            usort($activities, function($a, $b) {
                                return $b['date'] <=> $a['date'];
                            });
                        @endphp
                        
                        @if(count($activities) > 0)
                            <div class="timeline">
                                @foreach($activities as $activity)
                                    <div class="timeline-item mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <div class="rounded-circle bg-{{ $activity['color'] }} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas {{ $activity['icon'] }} text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1">{{ $activity['title'] }}</h6>
                                                <p class="mb-1 text-muted">{{ $activity['description'] }}</p>
                                                
                                                @if(isset($activity['property_url']) && $activity['property_url'] !== '#')
                                                    <a href="{{ $activity['property_url'] }}" class="btn btn-sm btn-success mt-2">
                                                        <i class="fas fa-home"></i> View Your Property
                                                    </a>
                                                @endif
                                                
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="far fa-clock"></i> 
                                                        {{ $activity['date']->format('M d, Y h:i A') }}
                                                        ({{ $activity['date']->diffForHumans() }})
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No activity yet. Join a draw to get started!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

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
    </div>

@push('footer')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
}
.progress {
    border-radius: 10px;
}
.progress-bar {
    border-radius: 10px;
}
</style>
@endpush