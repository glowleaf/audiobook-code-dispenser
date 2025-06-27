jQuery(document).ready(function($) {
    // Load initial stats
    loadStats();
    
    // Add book button click
    $('#acd-add-book-btn').on('click', function() {
        $('#acd-add-book-modal').show();
    });
    
    // Upload CSV with new book button click
    $('#acd-upload-csv-btn').on('click', function() {
        $('#acd-csv-upload-modal').show();
    });
    
    // Upload more codes button click
    $('.acd-upload-btn').on('click', function() {
        var bookId = $(this).data('book-id');
        var bookTitle = $(this).data('book-title');
        
        $('#acd-book-id').val(bookId);
        $('#acd-modal-book-title').text(bookTitle);
        $('#acd-upload-modal').show();
    });
    
    // Close modal
    $('.acd-modal-close, .acd-modal-cancel').on('click', function() {
        $('.acd-modal').hide();
        $('form').each(function() {
            this.reset();
        });
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).hasClass('acd-modal')) {
            $('.acd-modal').hide();
            $('form').each(function() {
                this.reset();
            });
        }
    });
    
    // Add book form submit
    $('#acd-add-book-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: acd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'acd_add_book',
                book_title: $('#acd-new-book-title').val(),
                nonce: acd_ajax.nonce
            },
            beforeSend: function() {
                $('#acd-add-book-form button[type="submit"]').prop('disabled', true).text('Adding...');
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
                alert('An error occurred while adding the book.');
            },
            complete: function() {
                $('#acd-add-book-form button[type="submit"]').prop('disabled', false).text('Add Book');
            }
        });
    });
    
    // CSV upload with new book form submit
    $('#acd-csv-upload-form').on('submit', function(e) {
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
                $('#acd-csv-upload-form button[type="submit"]').prop('disabled', true).text('Uploading...');
            },
            success: function(response) {
                if (response.success) {
                    alert('Success: Book "' + response.data.book_title + '" created and ' + response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred during upload.');
            },
            complete: function() {
                $('#acd-csv-upload-form button[type="submit"]').prop('disabled', false).text('Upload CSV');
            }
        });
    });
    
    // Upload more codes form submit
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