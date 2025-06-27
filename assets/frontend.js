jQuery(document).ready(function($) {
    var currentRegion = 'US';
    
    // Region toggle functionality
    $('.acd-region-option').on('click', function() {
        $('.acd-region-option').removeClass('active');
        $(this).addClass('active');
        currentRegion = $(this).data('region');
        
        // Update book options based on region
        updateBookOptions();
        
        // Update Audible link
        var audibleLink = currentRegion === 'US' ? 'Audible.com' : 'Audible.co.uk';
        $('#acd-audible-link').text(audibleLink);
        
        // Update promo redemption link
        var promoLink = currentRegion === 'US' ? 'https://www.audible.com/acx-promo' : 'https://www.audible.co.uk/acx-promo';
        $('#acd-promo-link').attr('href', promoLink).text(promoLink);
        
        // Hide any previous messages
        hideMessages();
    });
    
    // Book selection change
    $('#acd-book-title').on('change', function() {
        updateBookOptions();
    });
    
    // Form submission
    $('#acd-code-form').on('submit', function(e) {
        e.preventDefault();
        
        var email = $('#acd-email').val();
        var bookId = $('#acd-book-title').val();
        
        if (!email || !bookId) {
            showError('Please fill in all fields.');
            return;
        }
        
        // Check if codes are available for selected region
        var selectedOption = $('#acd-book-title option:selected');
        var availableCodes = currentRegion === 'US' ? 
            parseInt(selectedOption.data('us-available')) : 
            parseInt(selectedOption.data('uk-available'));
            
        if (availableCodes === 0) {
            showError('No codes available for this book in the selected region.');
            return;
        }
        
        showLoading(true);
        hideMessages();
        
        $.ajax({
            url: acd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'acd_dispense_code',
                email: email,
                book_id: bookId,
                marketplace: currentRegion,
                nonce: acd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data.code);
                    
                    // Reset form
                    $('#acd-code-form')[0].reset();
                    updateBookOptions();
                } else {
                    showError(response.data);
                }
            },
            error: function() {
                showError('An error occurred. Please try again.');
            },
            complete: function() {
                showLoading(false);
            }
        });
    });
    
    function updateBookOptions() {
        $('#acd-book-title option').each(function() {
            var $option = $(this);
            if ($option.val() === '') return; // Skip placeholder option
            
            var usAvailable = parseInt($option.data('us-available')) || 0;
            var ukAvailable = parseInt($option.data('uk-available')) || 0;
            var available = currentRegion === 'US' ? usAvailable : ukAvailable;
            
            if (available === 0) {
                $option.prop('disabled', true);
                $option.text($option.text().replace(' (No codes available)', '') + ' (No codes available)');
            } else {
                $option.prop('disabled', false);
                $option.text($option.text().replace(' (No codes available)', ''));
            }
        });
    }
    
    function showLoading(show) {
        if (show) {
            $('#acd-loading').show();
            $('#acd-submit-btn').prop('disabled', true);
        } else {
            $('#acd-loading').hide();
            $('#acd-submit-btn').prop('disabled', false);
        }
    }
    
    function showSuccess(code) {
        $('#acd-success-message').show();
        $('#acd-code-display').show();
        $('#acd-code-value').text(code);
    }
    
    function showError(message) {
        $('#acd-error-message').show();
        $('#acd-error-text').text(message);
    }
    
    function hideMessages() {
        $('#acd-success-message').hide();
        $('#acd-error-message').hide();
        $('#acd-code-display').hide();
    }
    
    // Initialize
    updateBookOptions();
    hideMessages();
}); 