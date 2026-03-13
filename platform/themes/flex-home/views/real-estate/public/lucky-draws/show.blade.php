<div class="container my-5">
    <div class="row">
        <!-- Main Draw Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <!-- Property Image -->
                <div class="position-relative">
                    @if($draw->property && $draw->property->image)
                        <img src="{{ RvMedia::getImageUrl($draw->property->image) }}" 
                             class="card-img-top" style="height: 400px; object-fit: cover;" 
                             alt="{{ $draw->property->name }}">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" 
                             style="height: 400px;">
                            <i class="fas fa-home fa-5x text-muted"></i>
                        </div>
                    @endif
                    
                    <!-- Status Badge -->
                    <span class="badge bg-{{ $draw->status === 'active' ? 'success' : 'warning' }} position-absolute top-0 start-0 m-3">
                        {{ ucfirst($draw->status) }}
                    </span>
                </div>

                <div class="card-body">
                    <h1 class="card-title fw-bold mb-3">{{ $draw->name }}</h1>
                    
                    @if($draw->property)
                        <p class="text-muted mb-3">
                            <i class="fas fa-map-marker-alt"></i> {{ $draw->property->location }}
                        </p>
                    @endif

                    <div class="mb-4">
                        <h5 class="fw-bold mb-2">About This Draw</h5>
                        <p class="text-muted">{{ $draw->description }}</p>
                    </div>

                    @if($draw->property)
                        <div class="mb-4">
                            <h5 class="fw-bold mb-2">Property Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-home text-primary"></i> 
                                    <strong>Type:</strong> {{ $draw->property->type ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-bed text-primary"></i> 
                                    <strong>Bedrooms:</strong> {{ $draw->property->number_bedroom ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-bath text-primary"></i> 
                                    <strong>Bathrooms:</strong> {{ $draw->property->number_bathroom ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-ruler-combined text-primary"></i> 
                                    <strong>Area:</strong> {{ $draw->property->square ?? 'N/A' }} sq ft
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Draw Rules -->
                    <div class="alert alert-info">
                        <h6 class="fw-bold mb-2"><i class="fas fa-info-circle"></i> Draw Rules</h6>
                        <ul class="mb-0">
                            <li>One entry per membership credit</li>
                            <li>Winner selected randomly on draw date</li>
                            <li>Winner will be notified via email and phone</li>
                            <li>Property transfer process will begin immediately after winner verification</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Participants Section -->
            @if($draw->participants->where('payment_status', 'paid')->count() > 0)
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-users"></i> Participants ({{ $draw->participants->where('payment_status', 'paid')->count() }})
                        </h5>
                        <div class="row">
                            @foreach($draw->participants->where('payment_status', 'paid')->take(12) as $participant)
                                <div class="col-md-2 col-4 mb-3 text-center">
                                    @if($participant->account)
                                        <div class="mb-2">
                                            @if($participant->account->avatar_id)
                                                <img src="{{ $participant->account->avatar_url }}" 
                                                     class="rounded-circle" 
                                                     width="50" height="50" 
                                                     alt="{{ $participant->account->name }}">
                                            @else
                                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <span class="text-white fw-bold">{{ substr($participant->account->first_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $participant->account->first_name }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($draw->participants->where('payment_status', 'paid')->count() > 12)
                            <p class="text-center text-muted mb-0">
                                + {{ $draw->participants->where('payment_status', 'paid')->count() - 12 }} more participants
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Draw Stats Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Draw Statistics</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Prize Value</span>
                            <span class="fw-bold text-success">₹{{ number_format($draw->property_value/100000, 2) }}L</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Participants</span>
                            <span class="fw-bold">{{ $stats['total_participants'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Draw Type</span>
                            <span class="fw-bold">{{ ucfirst($draw->draw_type) }}</span>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Time Remaining</h6>
                        <div class="text-center">
                            @if($stats['days_left'] > 0)
                                <h2 class="fw-bold text-primary mb-0">{{ $stats['days_left'] }}</h2>
                                <small class="text-muted">Days Left</small>
                            @else
                                <h4 class="fw-bold text-warning mb-0">{{ $stats['time_left'] }}</h4>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <!-- Draw Dates -->
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Start Date</small>
                        <strong>{{ $draw->start_date->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">End Date</small>
                        <strong>{{ $draw->end_date->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div class="mb-4">
                        <small class="text-muted d-block mb-1">Draw Date</small>
                        <strong class="text-primary">{{ $draw->draw_date->format('M d, Y h:i A') }}</strong>
                    </div>

                    <!-- Action Button -->
                    @auth('account')
                        @php
                            $user = auth('account')->user();
                        @endphp
                        @if($userParticipation)
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle"></i> You're participating in this draw!
                                <div class="mt-2">
                                    <small>Joined: {{ $userParticipation->joined_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        @elseif($draw->status !== 'active')
                            <button class="btn btn-secondary w-100" disabled>
                                Draw Not Active
                            </button>
                        @elseif($user->hasActiveDraw())
                            <button class="btn btn-warning w-100" disabled title="Complete your current draw first">
                                <i class="fas fa-lock"></i> Active Draw Running
                            </button>
                        @elseif($user->draws_remaining <= 0)
                            <button class="btn btn-danger w-100" disabled title="No draws remaining">
                                <i class="fas fa-times"></i> No Credits Left
                            </button>
                        @else
                            <form action="{{ route('public.lucky-draws.join', $draw->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-ticket-alt"></i> Join Draw
                                </button>
                                <small class="text-muted d-block mt-2 text-center">
                                    {{ $user->draws_remaining }} credits remaining
                                </small>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('public.account.login') }}" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login to Join
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Share Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Share This Draw</h6>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                           target="_blank" class="btn btn-outline-primary btn-sm flex-fill">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($draw->name) }}" 
                           target="_blank" class="btn btn-outline-info btn-sm flex-fill">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($draw->name . ' - ' . url()->current()) }}" 
                           target="_blank" class="btn btn-outline-success btn-sm flex-fill">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('public.lucky-draws.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to All Draws
            </a>
        </div>
    </div>
</div>

@push('footer')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}
</style>
@endpush
