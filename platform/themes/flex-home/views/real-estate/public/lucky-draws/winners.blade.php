<div class="container my-5">
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">🏆 Reward Draw Winners</h1>
        <p class="lead text-muted">Celebrating our amazing winners who got their dream properties!</p>
    </div>

    <!-- Winners Grid -->
    @if($recentWinners->count() > 0)
        <div class="row">
            @foreach($recentWinners as $index => $winner)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <!-- Winner Badge -->
                            @if($index < 3)
                                <div class="position-absolute top-0 start-0 m-2">
                                    @if($index === 0)
                                        <span class="badge bg-warning text-dark">🥇 Latest Winner</span>
                                    @elseif($index === 1)
                                        <span class="badge bg-secondary">🥈 Recent</span>
                                    @else
                                        <span class="badge bg-info">🥉 Recent</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Winner Avatar -->
                            <div class="mb-3">
                                @if($winner['avatar'])
                                    <img src="{{ $winner['avatar'] }}" 
                                         class="rounded-circle border border-3 border-warning" 
                                         width="80" height="80" 
                                         alt="{{ $winner['name'] }}">
                                @else
                                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center border border-3 border-warning" 
                                         style="width: 80px; height: 80px;">
                                        <span class="text-white fw-bold fs-3">{{ substr($winner['name'], 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Winner Info -->
                            <h4 class="fw-bold mb-2">{{ $winner['name'] }}</h4>
                            
                            @if(isset($winner['city']))
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt"></i> {{ $winner['city'] }}
                                </p>
                            @endif

                            <!-- Draw Info -->
                            <div class="bg-light rounded p-3 mb-3">
                                <h6 class="fw-bold text-primary mb-1">{{ $winner['draw_name'] }}</h6>
                                <p class="text-muted mb-0 small">{{ Str::limit($winner['property_name'], 30) }}</p>
                            </div>

                            <!-- Winner Quote (for dummy winners) -->
                            @if(isset($winner['bio']) && $winner['bio'])
                                <blockquote class="blockquote-footer mb-3">
                                    <small class="text-muted fst-italic">"{{ $winner['bio'] }}"</small>
                                </blockquote>
                            @endif

                            <!-- Draw Date -->
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> 
                                Won on {{ \Carbon\Carbon::parse($winner['draw_date'])->format('M d, Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Load More Button (if needed) -->
        @if($recentWinners->count() >= 20)
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary" onclick="loadMoreWinners()">
                    <i class="fas fa-plus"></i> Load More Winners
                </button>
            </div>
        @endif

    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <i class="fas fa-trophy fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Winners Yet</h4>
            <p class="text-muted mb-4">Be the first to win a property through our reward draws!</p>
            <a href="{{ route('public.lucky-draws.index') }}" class="btn btn-primary">
                <i class="fas fa-ticket-alt"></i> Join Active Draws
            </a>
        </div>
    @endif

    <!-- Success Stories Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="bg-primary text-white rounded p-5 text-center">
                <h3 class="fw-bold mb-3">🎉 Join Our Success Stories!</h3>
                <p class="lead mb-4">Every month, we help people achieve their dream of property ownership through our reward draws.</p>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h2 class="fw-bold">{{ $recentWinners->count() }}+</h2>
                        <p class="mb-0">Happy Winners</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h2 class="fw-bold">100%</h2>
                        <p class="mb-0">Genuine Winners</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h2 class="fw-bold">{{ \Carbon\Carbon::now()->format('Y') }}</h2>
                        <p class="mb-0">Active Since</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('public.lucky-draws.index') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-rocket"></i> Start Your Journey
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- How Winners Are Selected -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="fw-bold text-center mb-4">🤔 How Are Winners Selected?</h3>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-random text-white fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">Random Selection</h5>
                        <p class="text-muted">Winners are selected randomly from all paid participants</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt text-white fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">Fair & Transparent</h5>
                        <p class="text-muted">Automated system ensures complete fairness</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-clock text-white fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">On Schedule</h5>
                        <p class="text-muted">Winners announced exactly on draw date</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-gift text-white fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">Instant Rewards</h5>
                        <p class="text-muted">Non-winners get credits for future purchases</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('footer')
<style>
.card {
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}
.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
.blockquote-footer {
    border: none;
    padding: 0;
}
</style>

<script>
function loadMoreWinners() {
    // This can be implemented to load more winners via AJAX
    alert('Loading more winners... (Feature can be implemented)');
}
</script>
@endpush