$(document).ready(function() {
    // Approve button click
    $(document).on('click', '.btn-approve', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var btn = $(this);
        
        if (confirm('Are you sure you want to approve this account?')) {
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: '/admin/real-estate/accounts/approve/' + id,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.error) {
                        alert(response.message);
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i>');
                    } else {
                        alert('Account approved successfully!');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert('Error approving account. Please try again.');
                    btn.prop('disabled', false).html('<i class="fa fa-check"></i>');
                }
            });
        }
    });
    
    // Reject button click
    $(document).on('click', '.btn-reject', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var btn = $(this);
        
        var reason = prompt('Enter rejection reason (optional):');
        
        if (reason !== null) {
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: '/admin/real-estate/accounts/reject/' + id,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    reason: reason
                },
                success: function(response) {
                    if (response.error) {
                        alert(response.message);
                        btn.prop('disabled', false).html('<i class="fa fa-times"></i>');
                    } else {
                        alert('Account rejected successfully!');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert('Error rejecting account. Please try again.');
                    btn.prop('disabled', false).html('<i class="fa fa-times"></i>');
                }
            });
        }
    });
});
