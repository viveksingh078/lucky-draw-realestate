@extends('core/base::layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">🏠 Property Purchase Requests</h4>
                    </div>
                    <div class="card-body">
                        @if($purchases->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Property</th>
                                            <th>Property Price</th>
                                            <th>Final Amount</th>
                                            <th>Discount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchases as $purchase)
                                        <tr>
                                            <td>{{ $purchase->id }}</td>
                                            <td>
                                                <strong>{{ $purchase->account->name }}</strong><br>
                                                <small class="text-muted">{{ $purchase->account->email }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $purchase->property_name }}</strong><br>
                                                <small class="text-muted">{{ $purchase->property_location }}</small>
                                            </td>
                                            <td><strong>₹{{ number_format($purchase->property_price, 0) }}</strong></td>
                                            <td><strong class="text-success">₹{{ number_format($purchase->final_amount, 0) }}</strong></td>
                                            <td>
                                                @if($purchase->total_discount > 0)
                                                    <span class="text-success">₹{{ number_format($purchase->total_discount, 0) }}</span>
                                                    @if($purchase->lost_draw_discount > 0)
                                                        <br><small>Lost Draw: ₹{{ number_format($purchase->lost_draw_discount, 0) }}</small>
                                                    @endif
                                                    @if($purchase->wallet_discount > 0)
                                                        <br><small>Wallet: ₹{{ number_format($purchase->wallet_discount, 0) }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No discount</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($purchase->status == 'pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                @elseif($purchase->status == 'approved')
                                                    <span class="badge badge-success">Approved</span>
                                                @else
                                                    <span class="badge badge-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $purchase->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('property-purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $purchases->links() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> No property purchase requests found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection