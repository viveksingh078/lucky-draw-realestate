<div class="container-fluid">
        <!-- Hero Section -->
        <div class="bg-primary text-white py-5 mb-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="display-4 fw-bold mb-3">🏆 Reward Property Draws</h1>
                        <p class="lead mb-4">Win your dream property! Join our reward draws and get a chance to own premium properties at just the membership fee.</p>
                        <div class="d-flex gap-3">
                            <div class="text-center">
                                <h3 class="fw-bold">{{ $activeDraws->count() }}</h3>
                                <small>Active Draws</small>
                            </div>
                            <div class="text-center">
                                <h3 class="fw-bold">{{ $recentWinners->count() }}</h3>
                                <small>Happy Winners</small>
                            </div>
                            <div class="text-center">
                                <h3 class="fw-bold">₹{{ number_format($activeDraws->sum('property_value'), 0) }}</h3>
                                <small>Total Prize Value</small>
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
                                    <div class="card h-100 shadow-sm border-0">
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
                                            
                                            <!-- Draw Type Badge -->
                                            <span class="badge bg-primary position-absolute top-0 start-0 m-2">
                                                {{ ucfirst($draw->draw_type) }} Draw
                                            </span>
                                            
                                            <!-- Time Left Badge -->
                                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">
                                                {{ $draw->end_date->diffForHumans() }}
                                            </span>
                                        </div>

                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title fw-bold">{{ $draw->name }}</h5>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-map-marker-alt"></i> 
                                                {{ $draw->property->location ?? 'Premium Location' }}
                                            </p>
                                            <p class="card-text small">{{ Str::limit($draw->description, 100) }}</p>

                                            <!-- Stats -->
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <div class="border-end">
                                                        <h6 class="fw-bold text-primary mb-0">₹{{ number_format($draw->property_value/100000, 1) }}L</h6>
                                                        <small class="text-muted">Prize Value</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="fw-bold text-warning mb-0">{{ $draw->participants->where('payment_status', 'paid')->count() }}</h6>
                                                    <small class="text-muted">Joined</small>
                                                </div>
                                            </div>

                                            <!-- Progress Bar -->
                                            @php
                                                $totalPool = $draw->participants->where('payment_status', 'paid')->sum('entry_fee_paid');
                                                $progressPercent = $draw->property_value > 0 ? min(($totalPool / $draw->property_value) * 100, 100) : 0;
                                            @endphp
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span>Pool: ₹{{ number_format($totalPool, 0) }}</span>
                                                    <span>{{ number_format($progressPercent, 1) }}%</span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $progressPercent }}%"></div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="mt-auto">
                                                <div class="d-grid gap-2">
                                                    <a href="{{ route('public.account.lucky-draws.show', $draw->id) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    @auth('account')
                                                        @php
                                                            $user = auth('account')->user();
                                                            $userJoined = $draw->participants->where('account_id', $user->id)->first();
                                                        @endphp
                                                        @if($userJoined)
                                                            <button class="btn btn-success btn-sm" disabled>
                                                                <i class="fas fa-check"></i> Already Joined
                                                            </button>
                                                        @elseif($user->hasActiveDraw())
                                                            <button class="btn btn-secondary btn-sm" disabled title="Complete your current draw first">
                                                                <i class="fas fa-lock"></i> Active Draw Running
                                                            </button>
                                                        @elseif($user->draws_remaining <= 0)
                                                            <button class="btn btn-danger btn-sm" disabled title="No draws remaining">
                                                                <i class="fas fa-times"></i> No Credits Left
                                                            </button>
                                                        @else
                                                            <a href="{{ route('public.account.lucky-draws.join', $draw->id) }}" 
                                                               class="btn btn-primary btn-sm">
                                                                <i class="fas fa-ticket-alt"></i> Join Draw ({{ $user->draws_remaining }} left)
                                                            </a>
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
                                            <p class="text-muted">{{ $draw->property->name ?? 'Premium Property' }}</p>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Prize Value</small>
                                                    <h6 class="fw-bold">₹{{ number_format($draw->property_value/100000, 1) }}L</h6>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Entry Fee</small>
                                                    <h6 class="fw-bold">₹{{ number_format($draw->entry_fee, 0) }}</h6>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <small class="text-muted">Starts {{ $draw->start_date->diffForHumans() }}</small>
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
                                View All Winners
                            </a>
                        </div>
                        <div class="row">
                            @foreach($recentWinners->take(6) as $winner)
                                <div class="col-lg-2 col-md-4 col-6 mb-3">
                                    <div class="card border-0 shadow-sm text-center">
                                        <div class="card-body p-3">
                                            <div class="mb-2">
                                                @if($winner['avatar'])
                                                    <img src="{{ $winner['avatar'] }}" 
                                                         class="rounded-circle" 
                                                         width="50" height="50" 
                                                         alt="{{ $winner['name'] }}">
                                                @else
                                                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <span class="text-white fw-bold">{{ substr($winner['name'], 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <h6 class="fw-bold mb-1">{{ $winner['name'] }}</h6>
                                            <small class="text-muted">{{ Str::limit($winner['property_name'], 20) }}</small>
                                            <div class="mt-2">
                                                <span class="badge bg-success">₹{{ number_format($winner['property_value']/100000, 1) }}L</span>
                                            </div>
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
                                <h5 class="fw-bold">Pay Entry Fee</h5>
                                <p class="text-muted">Pay the membership fee to join</p>
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
                                <h5 class="fw-bold">Win or Get Credit</h5>
                                <p class="text-muted">Win property or get purchase credits</p>
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
    transition: transform 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-5px);
}
.progress {
    border-radius: 10px;
}
.progress-bar {
    border-radius: 10px;
}
</style>
@endpush