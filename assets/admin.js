jQuery(document).ready(function($) {
    // Load initial stats
    loadStats();
    
    // Upload button click
    $('.acd-upload-btn').on('click', function() {
        var bookId = $(this).data('book-id');
        var bookTitle = $(this).data('book-title');
        
        $('#acd-book-id').val(bookId);
        $('#acd-modal-book-title').text(bookTitle);
        $('#acd-upload-modal').show();
    });
    
    // Close modal
    $('.acd-modal-close, .acd-modal-cancel').on('click', function() {
        $('#acd-upload-modal').hide();
        $('#acd-upload-form')[0].reset();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target.id === 'acd-upload-modal') {
            $('#acd-upload-modal').hide();
            $('#acd-upload-form')[0].reset();
        }
    });
    
    // Upload form submit
    $('#acd-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'acd_upload_csv');
        formData.append('nonce', acd_ajax.nonce);
        
        $.ajax({
            url: acd_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#acd-upload-form button[type="submit"]').prop('disabled', true).text('Uploading...');
            },
            success: function(response) {
                if (response.success) {
                    alert('Success: ' + response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred during upload.');
            },
            complete: function() {
                $('#acd-upload-form button[type="submit"]').prop('disabled', false).text('Upload Codes');
            }
        });
    });
    
    // Delete button click
    $('.acd-delete-btn').on('click', function() {
        var bookId = $(this).data('book-id');
        var bookTitle = $(this).data('book-title');
        
        if (confirm('Are you sure you want to delete "' + bookTitle + '" and all its codes?')) {
            $.ajax({
                url: acd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'acd_delete_book',
                    book_id: bookId,
                    nonce: acd_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Book deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        }
    });
    
    function loadStats() {
        $.ajax({
            url: acd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'acd_get_stats',
                nonce: acd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var stats = response.data;
                    $('#us-available').text(calculateUSAvailable());
                    $('#uk-available').text(calculateUKAvailable());
                    $('#total-dispensed').text(stats.dispensed_codes || 0);
                }
            }
        });
    }
    
    function calculateUSAvailable() {
        var total = 0;
        $('.acd-code-count').each(function() {
            var row = $(this).closest('tr');
            var usAvailable = parseInt(row.find('td:nth-child(2) .acd-code-count').text()) || 0;
            total += usAvailable;
        });
        return total;
    }
    
    function calculateUKAvailable() {
        var total = 0;
        $('.acd-code-count').each(function() {
            var row = $(this).closest('tr');
            var ukAvailable = parseInt(row.find('td:nth-child(4) .acd-code-count').text()) || 0;
            total += ukAvailable;
        });
        return total;
    }
}); 