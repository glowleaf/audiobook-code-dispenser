<div class="acd-widget-container">
    <div class="acd-widget-logo">🎧</div>
    <h1 class="acd-widget-title"><?php echo esc_html($title); ?></h1>
    <p class="acd-widget-subtitle"><?php echo esc_html($subtitle); ?></p>
    
    <div class="acd-region-toggle">
        <div class="acd-region-option active" data-region="US">🇺🇸 US Audible</div>
        <div class="acd-region-option" data-region="GB">🇬🇧 UK Audible</div>
    </div>
    
    <form id="acd-code-form" class="acd-form-section">
        <div class="acd-form-group">
            <label for="acd-email">Email Address</label>
            <input type="email" id="acd-email" name="email" required placeholder="Enter your email address">
        </div>
        
        <div class="acd-form-group">
            <label for="acd-book-title">Choose Your Book</label>
            <select id="acd-book-title" name="book_id" required>
                <option value="">Select a book...</option>
                <?php foreach ($books as $book): ?>
                    <option value="<?php echo $book->id; ?>" 
                            data-us-available="<?php echo $book->us_available; ?>" 
                            data-uk-available="<?php echo $book->uk_available; ?>">
                        <?php echo esc_html($book->title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="acd-btn" id="acd-submit-btn">Get My Free Code</button>
    </form>
    
    <div class="acd-loading" id="acd-loading" style="display: none;">
        <div class="acd-spinner"></div>
        <p>Processing your request...</p>
    </div>
    
    <div class="acd-success-message" id="acd-success-message" style="display: none;">
        <h3>🎉 Success!</h3>
        <p>Your audiobook code has been sent to your email and is displayed below:</p>
    </div>
    
    <div class="acd-code-display" id="acd-code-display" style="display: none;">
        <h4>Your Audiobook Code:</h4>
        <div class="acd-code-value" id="acd-code-value"></div>
        <p><strong>Instructions:</strong> Go to <span id="acd-audible-link">Audible.com</span>, sign in to your account, and redeem this code in the "Redeem a Gift or Promotional Code" section.</p>
        <p><strong>Promo codes can be redeemed at:</strong> <a href="https://www.audible.com/acx-promo" target="_blank" rel="noopener" id="acd-promo-link">https://www.audible.com/acx-promo</a></p>
    </div>
    
    <div class="acd-error-message" id="acd-error-message" style="display: none;">
        <h3>❌ Error</h3>
        <p id="acd-error-text">Something went wrong. Please try again.</p>
    </div>
</div> 