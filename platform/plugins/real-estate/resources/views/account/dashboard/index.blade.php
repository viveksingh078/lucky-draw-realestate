@extends('plugins/real-estate::account.layouts.skeleton')
@section('content')
  <div class="dashboard crop-avatar">
    <div class="container">
      <div class="row">
        <div class="col-md-3 mb-3 dn db-ns">
          <div class="mb3">
            <div class="sidebar-profile">
              <div class="avatar-container mb-2">
                <div class="profile-image">
                  <div class="avatar-view mt-card-avatar mt-card-avatar-circle" style="max-width: 150px">
                    <img src="{{ $user->avatar->url ? RvMedia::getImageUrl($user->avatar->url, 'thumb') : $user->avatar_url }}" alt="{{ $user->name }}" class="br-100" style="width: 150px;">
                    <div class="mt-overlay br2">
                      <span><i class="fa fa-edit"></i></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="f4 b">{{ $user->name }}</div>
              <div class="f6 mb3 light-gray-text">
                <i class="fas fa-envelope mr2"></i><a href="mailto:{{ $user->email }}" class="gray-text">{{ $user->email }}</a>
              </div>
              <div class="mb3">
                <div class="light-gray-text mb2">
                  <i class="fas fa-calendar-alt mr2"></i>{{ trans('plugins/real-estate::dashboard.joined_on', ['date' => $user->created_at->format('F d, Y')]) }}
                </div>
                @if ($user->dob)
                  <div class="light-gray-text mb2">
                    <i class="fas fa-child mr2"></i>{{ trans('plugins/real-estate::dashboard.dob', ['date' => $user->dob->format('F d, Y')]) }}
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
          <div class="col-md-9 mb-3">
            {!! apply_filters(ACCOUNT_TOP_STATISTIC_FILTER, null) !!}
              
              <!-- Wallet & Membership Info -->
              <div class="row mb-4">
                  <div class="col-md-4 mb-3">
                      <div class="white h-100">
                          <div class="br2 pa3 h-100 d-flex flex-column" style="box-shadow: 0 1px 1px #ccc; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 140px;">
                              <div class="media-body flex-grow-1">
                                  <div class="f3" style="color: white;">
                                      <span class="fw6">₹{{ number_format($user->wallet_balance, 0) }}</span>
                                      <span class="fr"><i class="fas fa-wallet"></i></span>
                                  </div>
                                  <p style="color: white; margin-bottom: 5px; font-weight: 600;">Wallet Balance</p>
                                  @if($user->wallet_on_hold > 0)
                                      <small style="color: rgba(255,255,255,0.9); display: block;">
                                          Available: ₹{{ number_format($user->wallet_balance - $user->wallet_on_hold, 0) }}
                                      </small>
                                      <small style="color: rgba(255,255,255,0.9); display: block;">
                                          On Hold: ₹{{ number_format($user->wallet_on_hold, 0) }}
                                      </small>
                                  @else
                                      <small style="color: rgba(255,255,255,0.9);">Fully available</small>
                                  @endif
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col-md-4 mb-3">
                      <div class="white h-100">
                          <div class="br2 pa3 h-100 d-flex flex-column" style="box-shadow: 0 1px 1px #ccc; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); min-height: 140px;">
                              <div class="media-body flex-grow-1">
                                  <div class="f3" style="color: white;">
                                      <span class="fw6">{{ $user->membershipPlan ? $user->membershipPlan->name : 'No Plan' }}</span>
                                      <span class="fr"><i class="fas fa-crown"></i></span>
                                  </div>
                                  <p style="color: white; margin-bottom: 5px; font-weight: 600;">Current Membership Plan</p>
                                  @if($user->membershipPlan)
                                      <small style="color: rgba(255,255,255,0.9); display: block;">
                                          {{ $user->draws_remaining ?? 0 }} {{ ($user->draws_remaining ?? 0) == 1 ? 'credit' : 'credits' }} available
                                      </small>
                                      <small style="color: rgba(255,255,255,0.9); display: block;">
                                          Use to join draws
                                      </small>
                                  @else
                                      <small style="color: rgba(255,255,255,0.9);">No active plan</small>
                                  @endif
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col-md-4 mb-3">
                      <div class="white h-100">
                          <div class="br2 pa3 h-100 d-flex flex-column" style="box-shadow: 0 1px 1px #ccc; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); min-height: 140px;">
                              <div class="media-body flex-grow-1">
                                  <div class="f3" style="color: white;">
                                      <span class="fw6">₹{{ number_format($user->available_discount, 0) }}</span>
                                      <span class="fr"><i class="fas fa-tags"></i></span>
                                  </div>
                                  <p style="color: white; margin-bottom: 5px; font-weight: 600;">Property Discount</p>
                                  @php
                                      $lostDrawsCount = $user->luckyDrawParticipations()
                                          ->whereHas('draw', function($q) use ($user) {
                                              $q->where('status', 'completed')
                                                ->where(function($q2) use ($user) {
                                                    $q2->where('winner_type', '!=', 'real')
                                                       ->orWhere('winner_id', '!=', $user->id);
                                                });
                                          })
                                          ->count();
                                      
                                      $wonDrawsCount = $user->luckyDrawParticipations()
                                          ->whereHas('draw', function($q) use ($user) {
                                              $q->where('status', 'completed')
                                                ->where('winner_type', 'real')
                                                ->where('winner_id', $user->id);
                                          })
                                          ->count();
                                  @endphp
                                  @if($user->available_discount > 0)
                                      <small style="color: rgba(255,255,255,0.9); display: block;">
                                          Use on property purchase
                                      </small>
                                      <small style="color: rgba(255,255,255,0.9); display: block;">
                                          {{ $lostDrawsCount }} {{ $lostDrawsCount == 1 ? 'loss' : 'losses' }} • {{ $wonDrawsCount }} {{ $wonDrawsCount == 1 ? 'win' : 'wins' }}
                                      </small>
                                  @else
                                      <small style="color: rgba(255,255,255,0.9);">Lose draws to earn discount</small>
                                  @endif
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              
              <!-- Activity Logs -->
              <div class="white br2 pa3 mb3" style="box-shadow: 0 1px 1px #ccc;">
                  <h4 class="f4 b mb3" style="color: #333;">📋 Activity Logs</h4>
                  
                  @php
                      $activities = [];
                      
                      // Add membership activation
                      if($user->membership_start_date) {
                          $activities[] = [
                              'icon' => 'fa-crown',
                              'bg_color' => '#d4edda',
                              'icon_color' => '#155724',
                              'title' => 'Membership Plan Purchased',
                              'description' => ($user->membershipPlan ? $user->membershipPlan->name : 'Plan') . ' Plan - ' . ($user->membershipPlan ? $user->membershipPlan->draws_allowed : 0) . ' draws credited',
                              'date' => $user->membership_start_date,
                          ];
                      }
                      
                      // Get all participations (including deleted ones would need audit log)
                      // For now, we'll show current participations
                      $allParticipations = $user->luckyDrawParticipations()->with('draw')->latest()->get();
                      
                      foreach($allParticipations as $participation) {
                          if($participation->draw) {
                              // Check if draw is completed
                              if($participation->draw->status === 'completed') {
                                  // Check if this user is the winner
                                  // Method 1: Check if draw's winner_id matches current user
                                  // Method 2: Check participant's is_winner field
                                  $drawWinnerId = $participation->draw->winner_id;
                                  $drawWinnerType = $participation->draw->winner_type;
                                  $currentUserId = $user->id;
                                  $participantIsWinner = $participation->is_winner;
                                  
                                  $isWinner = false;
                                  
                                  // If winner is a real user and winner_id matches current user
                                  if ($drawWinnerType === 'real' && $drawWinnerId == $currentUserId) {
                                      $isWinner = true;
                                  }
                                  
                                  // Also check participant's is_winner field as fallback
                                  if ($participantIsWinner === true || $participantIsWinner === 1 || $participantIsWinner === '1') {
                                      $isWinner = true;
                                  }
                                  
                                  if($isWinner) {
                                      // Won the draw
                                      $propertyName = $participation->draw->property ? $participation->draw->property->name : 'Premium Property';
                                      $propertyUrl = $participation->draw->property && $participation->draw->property->slug 
                                          ? route('public.properties') . '/' . $participation->draw->property->slug 
                                          : '#';
                                      
                                      $activities[] = [
                                          'icon' => 'fa-trophy',
                                          'bg_color' => '#d4edda',
                                          'icon_color' => '#155724',
                                          'title' => '🎉 Won Draw!',
                                          'description' => $participation->draw->name . ' - Congratulations! You won ' . $propertyName,
                                          'property_url' => $propertyUrl,
                                          'property_name' => $propertyName,
                                          'date' => $participation->draw->draw_date,
                                      ];
                                  } else {
                                      // Lost the draw
                                      $activities[] = [
                                          'icon' => 'fa-times-circle',
                                          'bg_color' => '#f8d7da',
                                          'icon_color' => '#721c24',
                                          'title' => 'Draw Completed',
                                          'description' => $participation->draw->name . ' - Better luck next time!',
                                          'date' => $participation->draw->draw_date,
                                      ];
                                  }
                              }
                              
                              // Joined draw
                              $activities[] = [
                                  'icon' => 'fa-ticket-alt',
                                  'bg_color' => '#cfe2ff',
                                  'icon_color' => '#084298',
                                  'title' => 'Joined Draw',
                                  'description' => $participation->draw->name . ' - 1 credit used',
                                  'date' => $participation->created_at,
                              ];
                          }
                      }
                      
                      // Note: To track "Left Draw" events, we would need an audit log table
                      // For now, we can add a note about this
                      
                      // Sort by date descending
                      usort($activities, function($a, $b) {
                          return $b['date'] <=> $a['date'];
                      });
                  @endphp
                  
                  @if(count($activities) > 0)
                      <div class="activity-timeline">
                          @foreach($activities as $activity)
                              <div class="mb3 pb3" style="border-bottom: 1px solid #eee;">
                                  <div class="flex items-start">
                                      <div class="mr3">
                                          <div class="br-100 flex items-center justify-center" style="width: 35px; height: 35px; background: {{ $activity['bg_color'] }};">
                                              <i class="fas {{ $activity['icon'] }}" style="color: {{ $activity['icon_color'] }};"></i>
                                          </div>
                                      </div>
                                      <div class="flex-auto">
                                          <div class="f5 b mb1" style="color: #333;">{{ $activity['title'] }}</div>
                                          <div class="f6 mb1" style="color: #666;">{{ $activity['description'] }}</div>
                                          
                                          @if(isset($activity['property_url']) && $activity['property_url'] !== '#')
                                              <a href="{{ $activity['property_url'] }}" class="btn btn-sm" style="background: #28a745; color: white; padding: 5px 15px; border-radius: 4px; text-decoration: none; display: inline-block; margin-top: 8px;">
                                                  <i class="fas fa-home"></i> View Your Property
                                              </a>
                                          @endif
                                          
                                          <div class="f7 mt2" style="color: #999;">
                                              <i class="far fa-clock mr1"></i>
                                              {{ $activity['date']->format('M d, Y h:i A') }}
                                              ({{ $activity['date']->diffForHumans() }})
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          @endforeach
                      </div>
                      
                      @if(count($activities) > 15)
                          <div class="tc mt3">
                              <small style="color: #999;">Showing recent {{ count($activities) }} activities</small>
                          </div>
                      @endif
                  @else
                      <div class="tc pa4" style="color: #999;">
                          <i class="fas fa-history fa-3x mb3" style="opacity: 0.3;"></i>
                          <div class="f5 b mb2">No Activity Yet</div>
                          <div class="f6">Join a draw to see your activity here!</div>
                      </div>
                  @endif
              </div>
          </div>
      </div>
    </div>
    @include('plugins/real-estate::account.modals.avatar')
  </div>
@endsection
