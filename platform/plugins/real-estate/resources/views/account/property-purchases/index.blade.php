@extends('plugins/real-estate::account.layouts.skeleton')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">🏠 My Property Purchases</h2>
            
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if($purchases->count() > 0)
                <div class="row">
                    @foreach($purchases as $purchase)
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">{{ $purchase->property_name }}</h5>
                                    {!! $purchase->status_badge !!}
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt"></i> {{ $purchase->property_location }}
                                    </p>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Property Price</small>
                                            <div class="font-weight-bold">₹{{ number_format($purchase->property_price, 0) }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Final Amount</small>
                                            <div class="font-weight-bold text-success">₹{{ number_format($purchase->final_amount, 0) }}</div>
                                        </div>
                                    </div>
                                    
                                    @if($purchase->total_discount > 0)
                                        <div class="alert alert-success py-2">
                                            <small><strong>Total Savings:</strong> ₹{{ number_format($purchase->total_discount, 0) }}</small>
                                            @if($purchase->lost_draw_discount > 0)
                                                <br><small>Lost Draw Discount: ₹{{ number_format($purchase->lost_draw_discount, 0) }}</small>
                                            @endif
                                            @if($purchase->wallet_discount > 0)
                                                <br><small>Wallet Discount: ₹{{ number_format($purchase->wallet_discount, 0) }}</small>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <div class="text-muted small">
                                        <i class="fas fa-calendar"></i> Requested: {{ $purchase->created_at->format('M d, Y h:i A') }}
                                        @if($purchase->approved_at)
                                            <br><i class="fas fa-check"></i> Approved: {{ $purchase->approved_at->format('M d, Y h:i A') }}
                                        @endif
                                        @if($purchase->rejected_at)
                                            <br><i class="fas fa-times"></i> Rejected: {{ $purchase->rejected_at->format('M d, Y h:i A') }}
                                        @endif
                                    </div>
                                    
                                    @if($purchase->admin_notes)
                                        <div class="mt-2">
                                            <small class="text-muted"><strong>Admin Notes:</strong></small>
                                            <div class="small">{{ $purchase->admin_notes }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer">
                                    @if($purchase->property)
                                        <a href="{{ $purchase->property->url }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> View Property
                                        </a>
                                    @endif
                                    
                                    @if($purchase->isPending())
                                        <span class="badge badge-warning">Pending Admin Approval</span>
                                    @elseif($purchase->isApproved())
                                        <span class="badge badge-success">Purchase Approved</span>
                                    @elseif($purchase->isRejected())
                                        <span class="badge badge-danger">Purchase Rejected</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $purchases->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-home fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Property Purchases Yet</h4>
                    <p class="text-muted">You haven't made any property purchase requests yet.</p>
                    <a href="{{ route('public.properties') }}" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Properties
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection