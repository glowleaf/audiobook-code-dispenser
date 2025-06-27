<div class="wrap">
    <h1>Audiobook Code Dispenser</h1>
    
    <div class="acd-stats-section">
        <h2>Statistics</h2>
        <div class="acd-stats-grid">
            <div class="acd-stat-card">
                <h3>Total Books</h3>
                <span class="acd-stat-number"><?php echo count($books); ?></span>
            </div>
            <div class="acd-stat-card">
                <h3>Available US Codes</h3>
                <span class="acd-stat-number" id="us-available">0</span>
            </div>
            <div class="acd-stat-card">
                <h3>Available UK Codes</h3>
                <span class="acd-stat-number" id="uk-available">0</span>
            </div>
            <div class="acd-stat-card">
                <h3>Total Dispensed</h3>
                <span class="acd-stat-number" id="total-dispensed">0</span>
            </div>
        </div>
    </div>

    <div class="acd-books-section">
        <h2>Manage Books & Codes</h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>US Codes Available</th>
                    <th>US Codes Dispensed</th>
                    <th>UK Codes Available</th>
                    <th>UK Codes Dispensed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><strong><?php echo esc_html($book->title); ?></strong></td>
                    <td>
                        <span class="acd-code-count <?php echo $book->us_available == 0 ? 'acd-no-codes' : ''; ?>">
                            <?php echo $book->us_available; ?>
                        </span>
                    </td>
                    <td><?php echo $book->us_dispensed; ?></td>
                    <td>
                        <span class="acd-code-count <?php echo $book->uk_available == 0 ? 'acd-no-codes' : ''; ?>">
                            <?php echo $book->uk_available; ?>
                        </span>
                    </td>
                    <td><?php echo $book->uk_dispensed; ?></td>
                    <td>
                        <button class="button acd-upload-btn" data-book-id="<?php echo $book->id; ?>" data-book-title="<?php echo esc_attr($book->title); ?>">
                            Upload Codes
                        </button>
                        <button class="button button-link-delete acd-delete-btn" data-book-id="<?php echo $book->id; ?>" data-book-title="<?php echo esc_attr($book->title); ?>">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Upload Modal -->
<div id="acd-upload-modal" class="acd-modal" style="display: none;">
    <div class="acd-modal-content">
        <div class="acd-modal-header">
            <h3>Upload Codes for: <span id="acd-modal-book-title"></span></h3>
            <span class="acd-modal-close">&times;</span>
        </div>
        <div class="acd-modal-body">
            <form id="acd-upload-form" enctype="multipart/form-data">
                <input type="hidden" id="acd-book-id" name="book_id" value="">
                
                <div class="acd-form-group">
                    <label for="acd-marketplace">Marketplace:</label>
                    <select id="acd-marketplace" name="marketplace" required>
                        <option value="">Select Marketplace</option>
                        <option value="US">United States</option>
                        <option value="GB">United Kingdom</option>
                    </select>
                </div>
                
                <div class="acd-form-group">
                    <label for="acd-csv-file">CSV File:</label>
                    <input type="file" id="acd-csv-file" name="csv_file" accept=".csv" required>
                    <p class="description">Upload a CSV file with the format: Promo Code, Status, Marketplace, Generated On, Redemption Date, Shared</p>
                </div>
                
                <div class="acd-form-actions">
                    <button type="submit" class="button button-primary">Upload Codes</button>
                    <button type="button" class="button acd-modal-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div> 