@extends('core/base::layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">🏠 Property Purchase Request #{{ $purchase->id }}</h4>
                        <div class="card-header-actions">
                            <a href="{{ route('property-purchases.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- User Information -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">👤 User Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Name:</strong></td>
                                                <td>{{ $purchase->account->name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>{{ $purchase->account->email }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Phone:</strong></td>
                                                <td>{{ $purchase->account->phone }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>PAN Card:</strong></td>
                                                <td>{{ $purchase->account->pan_card_number ?? 'Not provided' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Membership:</strong></td>
                                                <td>{{ $purchase->account->membershipPlan->name ?? 'No Plan' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Information -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">🏠 Property Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Property:</strong></td>
                                                <td>{{ $purchase->property_name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Location:</strong></td>
                                                <td>{{ $purchase->property_location }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Property ID:</strong></td>
                                                <td>{{ $purchase->property_id }}</td>
                                            </tr>
                                            @if($purchase->property)
                                            <tr>
                                                <td><strong>View Property:</strong></td>
                                                <td>
                                                    <a href="{{ $purchase->property->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-external-link-alt"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">💰 Price Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Property Price:</strong></td>
                                                <td class="text-right">₹{{ number_format($purchase->property_price, 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>GST (18%):</strong></td>
                                                <td class="text-right">₹{{ number_format($purchase->gst_amount, 0) }}</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td><strong>Subtotal:</strong></td>
                                                <td class="text-right">₹{{ number_format($purchase->subtotal, 0) }}</td>
                                            </tr>
                                            @if($purchase->lost_draw_discount > 0)
                                            <tr class="text-success">
                                                <td><strong>Lost Draw Discount:</strong></td>
                                                <td class="text-right">-₹{{ number_format($purchase->lost_draw_discount, 0) }}</td>
                                            </tr>
                                            @endif
                                            @if($purchase->wallet_discount > 0)
                                            <tr class="text-info">
                                                <td><strong>Wallet Discount:</strong></td>
                                                <td class="text-right">-₹{{ number_format($purchase->wallet_discount, 0) }}</td>
                                            </tr>
                                            @endif
                                            <tr class="border-top">
                                                <td><strong>Total Discount:</strong></td>
                                                <td class="text-right text-success">-₹{{ number_format($purchase->total_discount, 0) }}</td>
                                            </tr>
                                            <tr class="border-top bg-light">
                                                <td><h5><strong>FINAL AMOUNT:</strong></h5></td>
                                                <td class="text-right"><h5><strong>₹{{ number_format($purchase->final_amount, 0) }}</strong></h5></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-info">
                                            <h6><strong>Savings Summary</strong></h6>
                                            <p class="mb-1">Original Price: ₹{{ number_format($purchase->subtotal, 0) }}</p>
                                            <p class="mb-1">Final Price: ₹{{ number_format($purchase->final_amount, 0) }}</p>
                                            <p class="mb-0 text-success"><strong>Total Saved: ₹{{ number_format($purchase->total_discount, 0) }}</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Actions -->
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0">📋 Status & Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>{!! $purchase->status_badge !!}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Requested:</strong></td>
                                                <td>{{ $purchase->created_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                            @if($purchase->approved_at)
                                            <tr>
                                                <td><strong>Approved:</strong></td>
                                                <td>{{ $purchase->approved_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Approved By:</strong></td>
                                                <td>{{ $purchase->approvedBy->name ?? 'N/A' }}</td>
                                            </tr>
                                            @endif
                                            @if($purchase->rejected_at)
                                            <tr>
                                                <td><strong>Rejected:</strong></td>
                                                <td>{{ $purchase->rejected_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                            @endif
                                            @if($purchase->admin_notes)
                                            <tr>
                                                <td><strong>Admin Notes:</strong></td>
                                                <td>{{ $purchase->admin_notes }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        @if($purchase->isPending())
                                            <div class="text-center">
                                                <h6>Actions Required</h6>
                                                <form action="{{ route('property-purchases.approve', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this purchase request?');">
                                                    @csrf
                                                    <div class="form-group">
                                                        <textarea name="notes" class="form-control mb-2" placeholder="Add approval notes (optional)" rows="2"></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fa fa-check"></i> Approve Purchase
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('property-purchases.reject', $purchase->id) }}" method="POST" class="d-inline ml-2" onsubmit="return confirm('Are you sure you want to reject this purchase request? Discounts will be refunded.');">
                                                    @csrf
                                                    <div class="form-group">
                                                        <textarea name="reason" class="form-control mb-2" placeholder="Rejection reason (required)" rows="2" required></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fa fa-times"></i> Reject Purchase
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="alert alert-info text-center">
                                                <h6>Request Already Processed</h6>
                                                <p class="mb-0">This purchase request has been {{ $purchase->status }}.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection