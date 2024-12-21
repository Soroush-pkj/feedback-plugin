<?php
if (!defined('ABSPATH')) {
    exit;
}

define('FEEDBACK_API_KEY', 'sf_GyhwFYweoxgVEXLxF0IbCSyzCWyfp2qU896vpN27NtxFejSZxkV7UhiNT9vQgwwDTyv5tRCiQSyi1QLSu9vhOA39VtkfyIjlbwHqNuJAc2IUxb906ckwjzWeeUuaS');


add_action('rest_api_init', ['Feedback_API', 'register_endpoints']);

class Feedback_API {

   
    public static function register_endpoints() {
        register_rest_route('feedback/v1', '/feedbacks', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_feedbacks'],
            'permission_callback' => [__CLASS__, 'verify_api_key'],
        ]);

        register_rest_route('feedback/v1', '/feedbacks/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [__CLASS__, 'delete_feedback'],
            'permission_callback' => [__CLASS__, 'verify_api_key'],
        ]);
    }

    
    public static function verify_api_key(WP_REST_Request $request) {
        $api_key = $request->get_header('api-key');

        if ($api_key && $api_key === FEEDBACK_API_KEY) {
            return true;
        }

        return new WP_Error(
            'unauthorized',
            'Invalid API Key.',
            ['status' => 401]
        );
    }

    
    public static function get_feedbacks(WP_REST_Request $request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'feedback';

        // Pagination
        $page = max(1, (int) $request->get_param('page'));
        $per_page = max(10, (int) $request->get_param('per_page'));
        $offset = ($page - 1) * $per_page;

        // Optional search query
        $search = sanitize_text_field($request->get_param('search'));
        $where = '';
        $params = [$offset, $per_page];

        if ($search) {
            $where = "WHERE name LIKE %s OR email LIKE %s";
            array_unshift($params, "%$search%", "%$search%");
        }

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name $where ORDER BY created_at ASC LIMIT %d, %d",
            ...$params
        );

        $results = $wpdb->get_results($query);

        if (empty($results)) {
            return new WP_REST_Response(['message' => 'No results found'], 200);
        }

        return new WP_REST_Response($results, 200);
    }

    public static function delete_feedback(WP_REST_Request $request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'feedback';
        $feedback_id = (int) $request->get_param('id');

        $feedback = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $feedback_id));

        if (!$feedback) {
            return new WP_REST_Response(['message' => 'Feedback not found.'], 404);
        }

        $deleted = $wpdb->delete($table_name, ['id' => $feedback_id]);

        if ($deleted) {
            return new WP_REST_Response(['message' => 'Feedback deleted successfully.'], 200);
        } else {
            return new WP_REST_Response(['message' => 'Error deleting feedback.'], 500);
        }
    }
}
