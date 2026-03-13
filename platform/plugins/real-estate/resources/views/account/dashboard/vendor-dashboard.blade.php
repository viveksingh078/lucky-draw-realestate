@extends('plugins/real-estate::account.layouts.skeleton')

@section('content')

<style>
    /* 🔥 FIX: Account Information text white issue */
    .account-info-box,
    .account-info-box div,
    .account-info-box strong,
    .account-info-box span {
        color: #000 !important;
    }

    .account-info-box .badge {
        color: #0c0b0b !important;
    }
</style>

<div class="dashboard crop-avatar">
    <div class="container">
        <div class="row">

            {{-- ================= LEFT SIDEBAR ================= --}}
            <div class="col-md-3 mb-3 dn db-ns">
                <div class="sidebar-profile">
                    <div class="avatar-container mb-3">
                        <div class="avatar-view mt-card-avatar mt-card-avatar-circle" style="max-width:150px">
                            <img src="{{ $user->avatar->url ? RvMedia::getImageUrl($user->avatar->url,'thumb') : $user->avatar_url }}"
                                 alt="{{ $user->name }}"
                                 class="br-100"
                                 style="width:150px;">
                            <div class="mt-overlay br2">
                                <span><i class="fa fa-edit"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="f4 b">{{ $user->name }}</div>

                    <div class="f6 mb2 light-gray-text">
                        <i class="fas fa-briefcase mr2"></i> {{ $user->company }}
                    </div>

                    <div class="f6 mb3 light-gray-text">
                        <i class="fas fa-envelope mr2"></i>
                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                    </div>

                    <div class="light-gray-text">
                        <i class="fas fa-calendar-alt mr2"></i>
                        Joined on {{ $user->created_at->format('F d, Y') }}
                    </div>
                </div>
            </div>

            {{-- ================= RIGHT CONTENT ================= --}}
            <div class="col-md-9 mb-3">

                {{-- ===== STATS ===== --}}
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="br2 pa3 text-white"
                             style="background:linear-gradient(135deg,#667eea,#764ba2);min-height:140px;">
                            <div class="f3 fw6">
                                {{ $totalProperties }}
                                <span class="fr"><i class="fas fa-home"></i></span>
                            </div>
                            <p class="fw6 mb1">Total Properties</p>
                            <small>Listed on platform</small>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="br2 pa3 text-white"
                             style="background:linear-gradient(135deg,#4facfe,#00f2fe);min-height:140px;">
                            <div class="f3 fw6">
                                {{ $activeProperties }}
                                <span class="fr"><i class="fas fa-check-circle"></i></span>
                            </div>
                            <p class="fw6 mb1">Active Properties</p>
                            <small>Currently visible</small>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="br2 pa3 text-white"
                             style="background:linear-gradient(135deg,#f093fb,#f5576c);min-height:140px;">
                            <div class="f3 fw6">
                                {{ $pendingProperties }}
                                <span class="fr"><i class="fas fa-clock"></i></span>
                            </div>
                            <p class="fw6 mb1">Pending Approval</p>
                            <small>Awaiting admin review</small>
                        </div>
                    </div>
                </div>

                {{-- ===== QUICK ACTIONS ===== --}}
                <div class="white br2 pa3 mb3">
                    <h4 class="f4 b mb3">⚡ Quick Actions</h4>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('public.account.properties.create') }}"
                               class="btn btn-primary btn-block">
                                <i class="fas fa-plus-circle mr2"></i> Add New Property
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('public.account.properties.index') }}"
                               class="btn btn-secondary btn-block">
                                <i class="fas fa-list mr2"></i> View All Properties
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ===== RECENT PROPERTIES ===== --}}
                <div class="white br2 pa3 mb3">
                    <h4 class="f4 b mb3">🏠 Recent Properties</h4>

                    @if($recentProperties->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($recentProperties as $property)
                                    <tr>
                                        <td>
                                            <strong>{{ Str::title($property->name) }}</strong><br>
                                            <small>{{ Str::title($property->location) }}</small>
                                        </td>
                                        <td>{{ $property->type }}</td>
                                        <td>₹{{ number_format($property->price) }}</td>
                                        <td>
                                            @if($property->moderation_status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif($property->moderation_status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @else
                                                <span class="badge badge-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>{{ $property->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('public.account.properties.edit',$property->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No properties found.</p>
                    @endif
                </div>

                {{-- ===== ACCOUNT INFORMATION (FIXED) ===== --}}
                <div class="white br2 pa3 mb3 account-info-box">
                    <h4 class="f4 b mb3">👤 Account Information</h4>
                    <div class="row">
                        <div class="col-md-6 mb-2"><strong>Company:</strong> {{ $user->company }}</div>
                        <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $user->email }}</div>
                        <div class="col-md-6 mb-2"><strong>Phone:</strong> {{ $user->phone }}</div>
                        <div class="col-md-6 mb-2">
                            <strong>Account Type:</strong>
                            <span class="badge badge-info">Vendor</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Status:</strong>
                            <span class="badge badge-success">Approved</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Member Since:</strong>
                            {{ $user->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('plugins/real-estate::account.modals.avatar')
</div>
@endsection
