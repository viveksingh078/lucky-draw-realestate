<div class="container-fluid">
        <!-- Hero Section -->
        <div class="bg-primary text-white py-5 mb-5">
            <div class="container">
                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>{{ session('success') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <!-- Error Message -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>{{ session('error') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="display-4 fw-bold mb-3">🏆 Reward Property Draws</h1>
                        <p class="lead mb-4">Win your dream property! Join our reward draws and get a chance to own premium properties at just the membership fee.</p>
                       <div class="row text-center mt-4">
    <div class="col-6">
        <h3 class="fw-bold mb-0">{{ $activeDraws->count() }}</h3>
        <small class="text-white-50">Active Draws</small>
    </div>

    <div class="col-6">
        <h3 class="fw-bold mb-0">{{ $recentWinners->count() }}</h3>
        <small class="text-white-50">Happy Winners</small>
    </div>
</div>

                    </div>
                    <div class="col-lg-4 text-center">
                        <i class="fas fa-trophy" style="font-size: 8rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Active Draws Section -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold">🎯 Active Draws - Join Now!</h2>
                        @auth('account')
                            <a href="{{ route('public.account.lucky-draws') }}" class="btn btn-outline-primary">
                                <i class="fas fa-user"></i> My Draws
                            </a>
                        @else
                            <a href="{{ route('public.account.login') }}" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login to Join
                            </a>
                        @endauth
                    </div>

                    @if($activeDraws->count() > 0)
                        <div class="row">
                            @foreach($activeDraws as $draw)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm border-0 position-relative">
                                        <!-- Badges positioned relative to card -->
                                        <span class="badge bg-primary position-absolute m-2" style="top: 0; left: 0; z-index: 100;">
                                            {{ ucfirst($draw->draw_type) }} Draw
                                        </span>
                                        
                                        <span class="badge bg-warning text-dark position-absolute m-2" style="top: 0; right: 0; z-index: 100;">
                                            {{ $draw->end_date->diffForHumans() }}
                                        </span>
                                        
                                        <!-- Property Image -->
                                        <div class="position-relative">
                                            @if($draw->property && $draw->property->image)
                                                <img src="{{ RvMedia::getImageUrl($draw->property->image) }}" 
                                                     class="card-img-top" style="height: 200px; object-fit: cover;" 
                                                     alt="{{ $draw->property->name }}">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="height: 200px;">
                                                    <i class="fas fa-home fa-3x text-muted"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title fw-bold">{{ $draw->name }}</h5>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-map-marker-alt"></i> 
                                                {{ $draw->property->location ?? 'Premium Location' }}
                                            </p>
                                            <p class="card-text small mb-3">{{ Str::limit($draw->description, 100) }}</p>

                                            <!-- Prize and Timer - Side by Side -->
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <div class="p-3 rounded h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                        <div class="text-white text-center">
                                                            <i class="fas fa-trophy fa-2x mb-2"></i>
                                                            <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">{{ Str::title($draw->property->name ?? 'Premium Property') }}</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    @php
                                                        $drawDate = $draw->draw_date;
                                                        $now = now();
                                                        $daysLeft = $now->diffInDays($drawDate, false);
                                                        $hoursLeft = $now->diffInHours($drawDate, false) % 24;
                                                    @endphp
                                                    <div class="p-3 rounded h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                                        <div class="text-white text-center">
                                                            <i class="fas fa-clock fa-2x mb-2"></i>
                                                            @if($daysLeft > 0)
                                                                <h5 class="fw-bold mb-0">{{ $daysLeft }}</h5>
                                                                <small>{{ $daysLeft == 1 ? 'Day Left' : 'Days Left' }}</small>
                                                            @elseif($hoursLeft > 0)
                                                                <h5 class="fw-bold mb-0">{{ $hoursLeft }}</h5>
                                                                <small>{{ $hoursLeft == 1 ? 'Hour Left' : 'Hours Left' }}</small>
                                                            @else
                                                                <h6 class="fw-bold mb-0">Soon!</h6>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="mt-auto">
                                                <div class="d-grid gap-2">
                                                    <a href="{{ route('public.lucky-draws.show', $draw->id) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    @auth('account')
                                                        @php
                                                            $user = auth('account')->user();
                                                            $userJoined = $draw->participants->where('account_id', $user->id)->first();
                                                        @endphp
                                                        @if($userJoined)
                                                            @php
                                                                $canLeave = $user->canLeaveDraw($draw);
                                                            @endphp
                                                            <button class="btn btn-success btn-sm" disabled>
                                                                <i class="fas fa-check"></i> Already Joined
                                                            </button>
                                                            @if($canLeave)
                                                                <form action="{{ route('public.lucky-draws.leave', $draw->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Are you sure you want to leave this draw? Your credit will be refunded.');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-warning btn-sm w-100">
                                                                        <i class="fas fa-sign-out-alt"></i> Leave Draw
                                                                    </button>
                                                                </form>
                                                                <small class="text-muted d-block mt-1">Can leave until {{ $draw->end_date->format('M d, h:i A') }}</small>
                                                            @else
                                                                <small class="text-muted d-block mt-1">Draw has ended</small>
                                                            @endif
                                                        @elseif($user->hasActiveDraw())
                                                            @php
                                                                $activeCount = $user->activeDraws()->count();
                                                                $maxConcurrent = $user->membershipPlan ? $user->membershipPlan->max_concurrent_draws : 1;
                                                            @endphp
                                                            <button class="btn btn-secondary btn-sm" disabled title="Maximum concurrent draws reached">
                                                                <i class="fas fa-lock"></i> Max Draws Reached ({{ $activeCount }}/{{ $maxConcurrent }})
                                                            </button>
                                                        @elseif($user->wallet_balance < ($user->membershipPlan ? $user->membershipPlan->credit_value : 10000))
                                                            <button class="btn btn-danger btn-sm w-100" disabled title="Insufficient balance">
                                                                <i class="fas fa-times"></i> Insufficient Balance
                                                            </button>
                                                        @else
                                                            <form action="{{ route('public.lucky-draws.join', $draw->id) }}" method="POST" class="w-100">
                                                                @csrf
                                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                                    <i class="fas fa-ticket-alt"></i> Join Draw
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        <a href="{{ route('public.account.login') }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fas fa-sign-in-alt"></i> Login to Join
                                                        </a>
                                                    @endauth
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-trophy fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Active Draws</h4>
                            <p class="text-muted">Check back soon for exciting new property draws!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upcoming Draws -->
            @if($upcomingDraws->count() > 0)
                <div class="row mb-5">
                    <div class="col-12">
                        <h3 class="fw-bold mb-4">🔜 Coming Soon</h3>
                        <div class="row">
                            @foreach($upcomingDraws as $draw)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="fas fa-clock fa-2x text-warning"></i>
                                            </div>
                                            <h5 class="fw-bold">{{ $draw->name }}</h5>
                                            <p class="text-muted mb-3">{{ Str::title($draw->property->name ?? 'Premium Property') }}</p>
                                            <div class="text-center mb-3">
                                                <small class="text-muted">Starts</small>
                                                <h6 class="fw-bold">{{ $draw->start_date->format('M d, Y') }}</h6>
                                            </div>
                                            <div class="mt-3">
                                                <small class="text-muted">Opens {{ $draw->start_date->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Winners -->
            @if($recentWinners->count() > 0)
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fw-bold">🎉 Recent Winners</h3>
                            <a href="{{ route('public.lucky-draws.winners') }}" class="btn btn-outline-primary">
                                <i class="fas fa-trophy"></i> View All Winners
                            </a>
                        </div>
                        <div class="row">
                            @foreach($recentWinners->take(6) as $index => $winner)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100 winner-card" style="overflow: hidden;">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center">
                                                <!-- Avatar Section -->
                                                <div class="col-auto">
                                                    <div class="position-relative">
                                                        @if($winner['avatar'])
                                                            <img src="{{ $winner['avatar'] }}" 
                                                                 class="rounded-circle border border-3 border-warning" 
                                                                 width="80" height="80" 
                                                                 style="object-fit: cover;"
                                                                 alt="{{ $winner['name'] }}">
                                                        @else
                                                            <div class="rounded-circle border border-3 border-warning d-inline-flex align-items-center justify-content-center" 
                                                                 style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                                <span class="text-white fw-bold fs-3">{{ substr($winner['name'], 0, 1) }}</span>
                                                            </div>
                                                        @endif
                                                        <!-- Winner Badge -->
                                                        @if($index === 0)
                                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.7rem;">
                                                                🏆 Latest
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Winner Info Section -->
                                                <div class="col">
                                                    <h5 class="fw-bold mb-1" style="color: #2c3e50;">{{ $winner['name'] }}</h5>
                                                    <p class="text-muted mb-2 small">
                                                        <i class="fas fa-trophy text-warning"></i> 
                                                        {{ Str::limit($winner['draw_name'], 30) }}
                                                    </p>
                                                    <p class="text-muted mb-2 small">
                                                        <i class="fas fa-home text-primary"></i> 
                                                        {{ Str::limit($winner['property_name'], 35) }}
                                                    </p>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="badge px-3 py-2" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); font-size: 0.9rem;">
                                                            🏆 Winner
                                                        </span>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar-alt"></i> 
                                                            {{ \Carbon\Carbon::parse($winner['draw_date'])->format('M d, Y') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Celebration Effect -->
                                            <div class="winner-celebration"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- How It Works -->
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="fw-bold text-center mb-5">🤔 How It Works</h3>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="text-center">
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 80px; height: 80px;">
                                    <span class="text-white fw-bold fs-3">1</span>
                                </div>
                                <h5 class="fw-bold">Choose Draw</h5>
                                <p class="text-muted">Select from active property draws</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="text-center">
                                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 80px; height: 80px;">
                                    <span class="text-white fw-bold fs-3">2</span>
                                </div>
                                <h5 class="fw-bold">Use Membership</h5>
                                <p class="text-muted">Join using your membership credits</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="text-center">
                                <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 80px; height: 80px;">
                                    <span class="text-white fw-bold fs-3">3</span>
                                </div>
                                <h5 class="fw-bold">Wait for Draw</h5>
                                <p class="text-muted">Winner selected automatically</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="text-center">
                                <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 80px; height: 80px;">
                                    <span class="text-white fw-bold fs-3">4</span>
                                </div>
                                <h5 class="fw-bold">Win Property!</h5>
                                <p class="text-muted">Winner gets the property</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('footer')
<style>
.card {
    transition: all 0.3s ease-in-out;
    overflow: visible !important;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
.progress {
    border-radius: 10px;
}
.progress-bar {
    border-radius: 10px;
}
.card > .badge {
    z-index: 100 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.card-body {
    position: relative;
    z-index: 1;
}
.col-lg-4, .col-md-6 {
    overflow: visible !important;
}
/* Gradient boxes hover effect */
.row.g-2 > div > div {
    transition: transform 0.2s ease;
}
.row.g-2 > div > div:hover {
    transform: scale(1.05);
}

/* Winner Card Styles */
.winner-card {
    position: relative;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-left: 4px solid #ffc107 !important;
    transition: all 0.3s ease-in-out;
}
.winner-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 40px rgba(255, 193, 7, 0.3) !important;
    border-left-width: 6px !important;
}
.winner-card .rounded-circle {
    transition: transform 0.3s ease;
}
.winner-card:hover .rounded-circle {
    transform: rotate(5deg) scale(1.1);
}
.winner-celebration {
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: radial-gradient(circle, rgba(255,193,7,0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.winner-card:hover .winner-celebration {
    animation: celebrate 1s ease-in-out infinite;
}
@keyframes celebrate {
    0%, 100% { transform: scale(1); opacity: 0.3; }
    50% { transform: scale(1.5); opacity: 0.1; }
}
</style>
@endpush