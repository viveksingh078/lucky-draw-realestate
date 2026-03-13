@extends('core/base::layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-trophy"></i> Manual Winner Selection
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Draw Information -->
                        <div class="alert alert-info">
                            <h4><strong>{{ $draw->name }}</strong></h4>
                            <p class="mb-0">
                                <strong>Property:</strong> {{ $draw->property->name ?? 'N/A' }}<br>
                                <strong>Draw Date:</strong> {{ $draw->draw_date->format('M d, Y H:i') }}<br>
                                <strong>Status:</strong> <span class="badge badge-success">{{ strtoupper($draw->status) }}</span>
                            </p>
                        </div>

                        <!-- Financial Summary -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Property Value</h6>
                                        <h3 class="text-primary">₹{{ number_format($propertyValue, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Total Pool Collected</h6>
                                        <h3 class="text-info">₹{{ number_format($totalPool, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Profit/Loss</h6>
                                        <h3 class="{{ $profitLoss >= 0 ? 'text-success' : 'text-danger' }}">
                                            ₹{{ number_format($profitLoss, 2) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Profit %</h6>
                                        <h3 class="{{ $profitPercentage >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($profitPercentage, 2) }}%
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($participants->isEmpty() && $dummyWinners->isEmpty())
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> No paid participants or dummy winners found.
                            </div>
                        @else
                            <!-- Winner Selection Form -->
                            <form action="{{ route('lucky-draws.set-winner', $draw->id) }}" method="POST" id="winnerSelectionForm">
                                @csrf
                                
                                <input type="hidden" name="winner_type" id="winner_type" value="real">
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle"></i> <strong>Important:</strong> Once you select a winner, the draw will be marked as completed and cannot be changed. Please review carefully before confirming.
                                </div>

                                <!-- Tabs for Real vs Dummy Winners -->
                                <ul class="nav nav-tabs mb-3" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="real-tab" data-toggle="tab" href="#real-winners" role="tab">
                                            <i class="fas fa-users"></i> Real Participants ({{ $participants->count() }})
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dummy-tab" data-toggle="tab" href="#dummy-winners" role="tab">
                                            <i class="fas fa-user-secret"></i> Dummy Winners ({{ $dummyWinners->count() }})
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <!-- Real Participants Tab -->
                                    <div class="tab-pane fade show active" id="real-winners" role="tabpanel">
                                        @if($participants->isEmpty())
                                            <div class="alert alert-info">No real participants in this draw.</div>
                                        @else
                                            <h5 class="mb-3">Select Winner from Real Participants</h5>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="50">Select</th>
                                                            <th>Participant Name</th>
                                                            <th>Email</th>
                                                            <th>Phone</th>
                                                            <th>Entry Fee Paid</th>
                                                            <th>Joined Date</th>
                                                            <th>Membership Plan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($participants as $participant)
                                                            <tr class="participant-row" data-account-id="{{ $participant->account_id }}" data-type="real">
                                                                <td class="text-center">
                                                                    <div class="custom-control custom-radio">
                                                                        <input type="radio" 
                                                                               id="winner_real_{{ $participant->account_id }}" 
                                                                               name="winner_id" 
                                                                               value="{{ $participant->account_id }}" 
                                                                               class="custom-control-input winner-radio"
                                                                               data-type="real">
                                                                        <label class="custom-control-label" for="winner_real_{{ $participant->account_id }}"></label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <strong>{{ $participant->account->first_name ?? 'N/A' }} {{ $participant->account->last_name ?? '' }}</strong>
                                                                </td>
                                                                <td>{{ $participant->account->email ?? 'N/A' }}</td>
                                                                <td>{{ $participant->account->phone ?? 'N/A' }}</td>
                                                                <td><span class="badge badge-success">₹{{ number_format($participant->entry_fee_paid, 2) }}</span></td>
                                                                <td>{{ $participant->created_at->format('M d, Y H:i') }}</td>
                                                                <td>
                                                                    @if($participant->account && $participant->account->membershipPlan)
                                                                        <span class="badge badge-info">{{ $participant->account->membershipPlan->name }}</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">No Plan</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Dummy Winners Tab -->
                                    <div class="tab-pane fade" id="dummy-winners" role="tabpanel">
                                        @if($dummyWinners->isEmpty())
                                            <div class="alert alert-info">No dummy winners available.</div>
                                        @else
                                            <h5 class="mb-3">Select Dummy Winner</h5>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="50">Select</th>
                                                            <th>Name</th>
                                                            <th>City</th>
                                                            <th>Bio/Testimonial</th>
                                                            <th>Avatar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($dummyWinners as $dummy)
                                                            <tr class="participant-row" data-dummy-id="{{ $dummy->id }}" data-type="dummy">
                                                                <td class="text-center">
                                                                    <div class="custom-control custom-radio">
                                                                        <input type="radio" 
                                                                               id="winner_dummy_{{ $dummy->id }}" 
                                                                               name="winner_id" 
                                                                               value="{{ $dummy->id }}" 
                                                                               class="custom-control-input winner-radio"
                                                                               data-type="dummy">
                                                                        <label class="custom-control-label" for="winner_dummy_{{ $dummy->id }}"></label>
                                                                    </div>
                                                                </td>
                                                                <td><strong>{{ $dummy->name }}</strong></td>
                                                                <td>{{ $dummy->city ?? 'N/A' }}</td>
                                                                <td>{{ Str::limit($dummy->bio ?? 'N/A', 50) }}</td>
                                                                <td>
                                                                    @if($dummy->avatar_url)
                                                                        <img src="{{ $dummy->avatar_url }}" alt="{{ $dummy->name }}" style="width: 40px; height: 40px; border-radius: 50%;">
                                                                    @else
                                                                        <span class="badge badge-secondary">No Avatar</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success btn-lg" id="confirmWinnerBtn" disabled>
                                        <i class="fas fa-check-circle"></i> Confirm Winner Selection
                                    </button>
                                    <a href="{{ route('lucky-draws.show', $draw->id) }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .participant-row {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .participant-row:hover {
            background-color: #f8f9fa;
        }
        .participant-row.selected {
            background-color: #d4edda !important;
            border-left: 4px solid #28a745;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-tabs .nav-link {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .nav-tabs .nav-link:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #495057;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmBtn = document.getElementById('confirmWinnerBtn');
            const form = document.getElementById('winnerSelectionForm');
            const winnerTypeInput = document.getElementById('winner_type');

            // Tab switching functionality
            const realTab = document.getElementById('real-tab');
            const dummyTab = document.getElementById('dummy-tab');
            const realPane = document.getElementById('real-winners');
            const dummyPane = document.getElementById('dummy-winners');

            // Real tab click
            realTab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update tabs
                realTab.classList.add('active');
                dummyTab.classList.remove('active');
                
                // Update panes
                realPane.classList.add('show', 'active');
                dummyPane.classList.remove('show', 'active');
                
                // Clear selection
                clearSelection();
            });

            // Dummy tab click
            dummyTab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update tabs
                dummyTab.classList.add('active');
                realTab.classList.remove('active');
                
                // Update panes
                dummyPane.classList.add('show', 'active');
                realPane.classList.remove('show', 'active');
                
                // Clear selection
                clearSelection();
            });

            // Make entire row clickable
            document.querySelectorAll('.participant-row').forEach(row => {
                row.addEventListener('click', function(e) {
                    // Don't trigger if clicking on the radio button itself
                    if (e.target.type === 'radio' || e.target.tagName === 'LABEL') {
                        return;
                    }
                    
                    const radio = this.querySelector('.winner-radio');
                    if (radio) {
                        radio.checked = true;
                        
                        // Update winner type based on selection
                        const type = radio.getAttribute('data-type');
                        winnerTypeInput.value = type;
                        
                        updateSelection();
                    }
                });
            });

            // Handle radio button changes
            document.querySelectorAll('.winner-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const type = this.getAttribute('data-type');
                    winnerTypeInput.value = type;
                    updateSelection();
                });
            });

            function updateSelection() {
                // Remove selected class from all rows
                document.querySelectorAll('.participant-row').forEach(row => {
                    row.classList.remove('selected');
                });
                
                // Add selected class to checked row
                const checkedRadio = document.querySelector('.winner-radio:checked');
                if (checkedRadio) {
                    const selectedRow = checkedRadio.closest('.participant-row');
                    selectedRow.classList.add('selected');
                    confirmBtn.disabled = false;
                }
            }

            function clearSelection() {
                document.querySelectorAll('.winner-radio').forEach(radio => {
                    radio.checked = false;
                });
                document.querySelectorAll('.participant-row').forEach(row => {
                    row.classList.remove('selected');
                });
                confirmBtn.disabled = true;
            }

            // Confirmation before submit
            form.addEventListener('submit', function(e) {
                const checkedRadio = document.querySelector('.winner-radio:checked');
                if (!checkedRadio) {
                    e.preventDefault();
                    alert('Please select a winner first!');
                    return false;
                }

                const selectedRow = checkedRadio.closest('.participant-row');
                const winnerName = selectedRow.querySelector('td:nth-child(2)').textContent.trim();
                const winnerType = winnerTypeInput.value;
                const typeLabel = winnerType === 'real' ? 'Real Participant' : 'Dummy Winner';
                
                if (!confirm(`Are you sure you want to select "${winnerName}" as the winner?\n\nType: ${typeLabel}\n\nThis action cannot be undone!`)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection
