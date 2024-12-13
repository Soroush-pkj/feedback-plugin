<?php
class Feedback_Admin
{
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'add_admin_page']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    public static function add_admin_page()
    {
        add_menu_page(
            'Feedback Report',
            'Feedback Report',
            'manage_feedback', // Custom capability
            'feedback-report',
            [__CLASS__, 'render_admin_page'],
            'dashicons-chart-bar',
            26
        );
    }

    public static function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_feedback-report') {
            return;
        }

        wp_enqueue_style('feedback-admin-style', plugin_dir_url(__FILE__) . '../assets/style.css');
        // wp_enqueue_script('feedback-chart-js', plugin_dir_url(__FILE__) . '../assets/chart.js', [], null, true);
        wp_enqueue_script('feedback-chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);

        wp_enqueue_script('feedback-admin-script', plugin_dir_url(__FILE__) . '../assets/admin.js', ['jquery'], null, true);
        wp_localize_script('feedback-admin-script', 'feedbackAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('feedback_admin_nonce'),
        ]);
    }

    public static function render_admin_page()
    {
?>
        <div class="wrap">
            <h1>Feedback Report</h1>
            <form method="GET" id="feedback-search-form">
                <input type="hidden" name="page" value="feedback-report">
                <input type="text" name="search" placeholder="Search by name or email">
                <button type="submit" class="button">Search</button>
            </form>

            <form method="POST" id="bulk-delete-form" action="<?php echo admin_url('admin-post.php'); ?>">

                <?php wp_nonce_field('feedback_admin_nonce'); ?> <!-- اضافه کردن nonce به فرم -->
                <input type="hidden" name="action" value="bulk_delete">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>

                            <th><input type="checkbox" id="select-all"></th>
                            <?php
                            $current_order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'desc' : 'asc';
                            ?>
                            <th><a href="?page=feedback-report&orderby=email&order=<?php echo $current_order; ?>">Email</a></th>
                            <th><a href="?page=feedback-report&orderby=name&order=<?php echo $current_order; ?>">Name</a></th>
                            <th><a href="?page=feedback-report&orderby=rating&order=<?php echo $current_order; ?>">Rating</a></th>
                            <th><a href="?page=feedback-report&orderby=date&order=<?php echo $current_order; ?>">Date</a></th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php self::list_feedback_entries(); ?>
                    </tbody>
                </table>
                <button type="submit" class="button button-primary">Delete Selected</button>
            </form>



            <h2>Average Rating Over the Last 7 Days</h2>
            <canvas id="feedback-chart" width="400" height="200"></canvas>
        </div>
<?php
    }


    private static function render_pagination($total_items, $items_per_page, $current_page)
    {
        $total_pages = ceil($total_items / $items_per_page);
    
        if ($total_pages <= 1) {
            return; // نیازی به صفحه‌بندی نیست
        }
    
        $page_links = [];
    
        for ($i = 1; $i <= $total_pages; $i++) {
            $page_links[] = sprintf(
                '<a class="pagination-link %s" href="?page=feedback-report&paged=%d">%d</a>',
                $i === $current_page ? 'current' : '',
                $i,
                $i
            );
        }
    
        echo '<div class="pagination">' . implode(' | ', $page_links) . '</div>';
    }
    
    private static function list_feedback_entries()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';

    // تعداد رکورد در هر صفحه
    $items_per_page = 5;

    // شماره صفحه فعلی
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? absint($_GET['paged']) : 1;

    // محاسبه offset
    $offset = ($current_page - 1) * $items_per_page;

    // Handle search query
    $search_query = '';
    if (!empty($_GET['search'])) {
        $search = sanitize_text_field($_GET['search']);
        $search_query = $wpdb->prepare("WHERE name LIKE %s OR email LIKE %s", "%$search%", "%$search%");
    }

    // Handle sorting
    $orderby_column = 'created_at'; // Default column
    $order_direction = 'DESC'; // Default order

    if (isset($_GET['orderby'])) {
        $allowed_columns = ['email', 'name', 'rating', 'date'];
        $orderby_column = in_array($_GET['orderby'], $allowed_columns) ? sanitize_text_field($_GET['orderby']) : 'created_at';
    }

    if (isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC'])) {
        $order_direction = strtoupper($_GET['order']);
    }

    $orderby = "{$orderby_column} {$order_direction}";

    // Fetch total count for pagination
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $search_query");

    // Fetch entries with pagination
    $entries = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name $search_query ORDER BY $orderby LIMIT %d OFFSET %d",
        $items_per_page,
        $offset
    ));

    // Render table rows
    foreach ($entries as $entry) {
        echo '<tr>';
        echo '<td><input type="checkbox" name="bulk_delete_ids[]" value="' . esc_attr($entry->id) . '"></td>';
        echo '<td>' . esc_html($entry->email) . '</td>';
        echo '<td>' . esc_html($entry->name) . '</td>';
        echo '<td>' . esc_html($entry->rating) . '</td>';
        echo '<td>' . esc_html($entry->created_at) . '</td>';
        echo '</tr>';
    }

    // Render pagination links
    self::render_pagination($total_items, $items_per_page, $current_page);
}




    public static function handle_bulk_delete()
    {
        if (!isset($_POST['bulk_delete_ids'])) {
            return;
        }

        // Verify nonce
        check_admin_referer('feedback_admin_nonce');

        // Ensure the user has the proper capabilities

        global $wpdb;
        $ids = array_map('intval', $_POST['bulk_delete_ids']);
        $table_name = $wpdb->prefix . 'feedback';

        // Delete selected feedback entries
        $wpdb->query("DELETE FROM $table_name WHERE id IN (" . implode(',', $ids) . ")");

        // Redirect to prevent resubmission on refresh
        wp_redirect(admin_url('admin.php?page=feedback-report'));
        exit;
    }



    public static function handle_chart_data()
    {
        check_ajax_referer('feedback_admin_nonce', '_ajax_nonce'); // امنیت

        global $wpdb;
        $table_name = $wpdb->prefix . 'feedback';

        // محاسبه میانگین امتیاز هر روز در 7 روز گذشته
        $results = $wpdb->get_results("
            SELECT DATE(created_at) as date, AVG(rating) as avg_rating
            FROM $table_name
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ");

        if ($results) {
            wp_send_json_success($results);
        } else {
            wp_send_json_error(['message' => 'No data available']);
        }
    }
}
