@extends('core/base::layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Recharge Request #{{ $recharge->id }}</h4>
                        <a href="{{ route('credit-recharges.index') }}" class="btn btn-secondary btn-sm float-right">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- User Details -->
                            <div class="col-md-6">
                                <h5>User Information</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Name</th>
                                        <td>{{ $recharge->account->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $recharge->account->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $recharge->account->phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Current Wallet Balance</th>
                                        <td><strong>₹{{ number_format($recharge->account->wallet_balance, 0) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Current Draws Remaining</th>
                                        <td><strong>{{ $recharge->account->draws_remaining }}</strong></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Recharge Details -->
                            <div class="col-md-6">
                                <h5>Recharge Details</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Plan</th>
                                        <td>{{ $recharge->membershipPlan->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td><strong class="text-success">₹{{ number_format($recharge->amount, 0) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Credits to Add</th>
                                        <td><strong>{{ $recharge->membershipPlan->draws_allowed }} draws</strong></td>
                                    </tr>
                                    <tr>
                                        <th>UTR Number</th>
                                        <td><code>{{ $recharge->payment_utr_number }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($recharge->status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($recharge->status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-danger">Rejected</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Submitted At</th>
                                        <td>{{ $recharge->created_at->format('M d, Y H:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Payment Screenshot -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Payment Screenshot</h5>
                                @if($recharge->payment_screenshot)
                                    <div class="text-center">
                                        <img src="{{ RvMedia::getImageUrl($recharge->payment_screenshot) }}" alt="Payment Screenshot" style="max-width: 600px; border: 2px solid #ddd; padding: 10px;">
                                    </div>
                                @else
                                    <div class="alert alert-warning">No screenshot uploaded</div>
                                @endif
                            </div>
                        </div>

                        <!-- Admin Actions -->
                        @if($recharge->isPending())
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Admin Actions</h5>
                                <form action="{{ route('credit-recharges.approve', $recharge->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    <div class="form-group">
                                        <label>Notes (optional)</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Approve this recharge? Credits will be added to user wallet.')">
                                        <i class="fa fa-check"></i> Approve Recharge
                                    </button>
                                </form>

                                <form action="{{ route('credit-recharges.reject', $recharge->id) }}" method="POST" style="display: inline-block; margin-left: 10px;">
                                    @csrf
                                    <div class="form-group">
                                        <label>Rejection Reason</label>
                                        <textarea name="reason" class="form-control" rows="2" placeholder="Enter reason for rejection..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Reject this recharge request?')">
                                        <i class="fa fa-times"></i> Reject Recharge
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif

                        <!-- Approval/Rejection Info -->
                        @if($recharge->isApproved())
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    <h5><i class="fa fa-check-circle"></i> Approved</h5>
                                    <p><strong>Approved By:</strong> {{ $recharge->approvedBy->name ?? 'N/A' }}</p>
                                    <p><strong>Approved At:</strong> {{ $recharge->approved_at->format('M d, Y H:i A') }}</p>
                                    @if($recharge->admin_notes)
                                        <p><strong>Notes:</strong> {{ $recharge->admin_notes }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($recharge->isRejected())
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-danger">
                                    <h5><i class="fa fa-times-circle"></i> Rejected</h5>
                                    <p><strong>Rejected At:</strong> {{ $recharge->rejected_at->format('M d, Y H:i A') }}</p>
                                    <p><strong>Reason:</strong> {{ $recharge->admin_notes }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
