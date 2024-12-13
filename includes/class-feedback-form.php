<?php
class Feedback_Form {
    public static function init() {
        add_shortcode('feedback-form', [__CLASS__, 'render_feedback_form']);
        add_action('wp_ajax_nopriv_submit_feedback', [__CLASS__, 'handle_form_submission']);
        add_action('wp_ajax_submit_feedback', [__CLASS__, 'handle_form_submission']);
    }

    public static function render_feedback_form() {
        ob_start();
        ?>
        <form id="feedback-form">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="feedback">Feedback:</label>
            <textarea id="feedback" name="feedback" required></textarea>
            <label for="rating">Rating:</label>
            <div class="star-rating" id="star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <span class="star" data-value="<?php echo $i; ?>">&#9733;</span>
                <?php endfor; ?>
            </div>
            <input type="hidden" id="rating" name="rating" value="" required>
            <button type="submit">Submit</button>
        </form>
        <div id="feedback-response"></div>
        <?php
        return ob_get_clean();
    }
    
    
    public static function handle_form_submission() {
        global $wpdb;

        // Validate and sanitize input
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $feedback = sanitize_textarea_field($_POST['feedback']);
        $rating = intval($_POST['rating']);

        if (empty($name) || empty($email) || empty($feedback) || $rating < 1 || $rating > 5) {
            wp_send_json_error(['message' => 'Invalid form submission.']);
        }

        $table_name = $wpdb->prefix . 'feedback';
        $wpdb->insert($table_name, [
            'name' => $name,
            'email' => $email,
            'feedback' => $feedback,
            'rating' => $rating,
            'created_at' => current_time('mysql'),
        ]);

        wp_send_json_success(['message' => 'Feedback submitted successfully.']);
    }
}
