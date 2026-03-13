@extends('core/base::layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">💰 Credit Recharge Requests</h4>
                    </div>
                    <div class="card-body">
                        @if($recharges->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Plan</th>
                                            <th>Amount</th>
                                            <th>UTR Number</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recharges as $recharge)
                                        <tr>
                                            <td>{{ $recharge->id }}</td>
                                            <td>
                                                <strong>{{ $recharge->account->name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $recharge->account->email ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $recharge->membershipPlan->name ?? 'N/A' }}</td>
                                            <td><strong>₹{{ number_format($recharge->amount, 0) }}</strong></td>
                                            <td><code>{{ $recharge->payment_utr_number }}</code></td>
                                            <td>
                                                @if($recharge->status == 'pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                @elseif($recharge->status == 'approved')
                                                    <span class="badge badge-success">Approved</span>
                                                @else
                                                    <span class="badge badge-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $recharge->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('credit-recharges.show', $recharge->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $recharges->links() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> No recharge requests found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
