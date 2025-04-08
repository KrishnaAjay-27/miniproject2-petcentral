// Add this to the JavaScript section that handles the order assignment
$(document).on('click', '.assign-btn', function() {
    var deid = $(this).data('deid');
    var orderId = $('#orderIdToAssign').val();
    var $btn = $(this);
    var $row = $btn.closest('tr');
    
    $.ajax({
        url: 'assign_order.php',
        type: 'POST',
        dataType: 'json',
        data: {
            order_id: orderId,
            deid: deid
        },
        beforeSend: function() {
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');
        },
        success: function(response) {
            if (response.status === 'success') {
                // Show success message
                toastr.success(response.message);
                
                // Update the count on the UI
                var $countBadge = $row.find('.order-count-badge');
                if ($countBadge.length) {
                    $countBadge.text(response.today_count + '/10');
                    
                    // Update progress bar if it exists
                    var $progressBar = $row.find('.progress-bar');
                    if ($progressBar.length) {
                        var percentage = (response.today_count / 10) * 100;
                        $progressBar.css('width', percentage + '%');
                        
                        if (response.limit_reached) {
                            $progressBar.removeClass('bg-success').addClass('bg-danger');
                        }
                    }
                }
                
                // If limit reached, hide assign button and show limit reached message
                if (response.limit_reached) {
                    $btn.hide();
                    $row.find('.status-cell').html('<span class="badge badge-danger">Unavailable</span>');
                    $row.find('.action-cell').append('<button class="btn btn-secondary btn-sm mt-1" disabled><i class="fas fa-ban mr-1"></i> Daily Limit Reached</button>');
                }
                
                // Refresh the available orders modal
                $('#availableOrdersModal').modal('hide');
                setTimeout(function() {
                    loadAvailableOrders($btn.data('pincode'));
                }, 1000);
            } else {
                // Show error message
                toastr.error(response.message);
                $btn.prop('disabled', false).html('<i class="fas fa-truck mr-1"></i> Assign Order');
            }
        },
        error: function() {
            toastr.error('An error occurred while assigning the order');
            $btn.prop('disabled', false).html('<i class="fas fa-truck mr-1"></i> Assign Order');
        }
    });
}); 
 
 