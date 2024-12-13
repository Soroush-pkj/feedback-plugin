<?php
class Feedback_Role_Manager {
    public static function init() {
        add_action('init', [__CLASS__, 'add_digital_marketer_role']);
        add_action('admin_menu', [__CLASS__, 'restrict_menu_access']);
        add_filter('map_meta_cap', [__CLASS__, 'grant_feedback_access'], 10, 4);
    }

    // ایجاد نقش Digital Marketer
    public static function add_digital_marketer_role() {
        if (!get_role('digital_marketer')) {
            add_role(
                'digital_marketer',
                'Digital Marketer',
                [
                    'read' => true, // دسترسی پایه
                ]
            );
        }

        // افزودن قابلیت به نقش Digital Marketer
        $role = get_role('digital_marketer');
        if ($role && !$role->has_cap('manage_feedback')) {
            $role->add_cap('manage_feedback'); // دسترسی به گزارشات
        }

        // اطمینان از اینکه نقش Administrator هم قابلیت را دارد
        $admin_role = get_role('administrator');
        if ($admin_role && !$admin_role->has_cap('manage_feedback')) {
            $admin_role->add_cap('manage_feedback');
        }
    }

    // محدود کردن دسترسی به منوی Feedback Report
    public static function restrict_menu_access() {
        if (!current_user_can('manage_feedback')) {
            remove_menu_page('feedback-report');
        }
    }

    // اطمینان از اینکه دسترسی‌ها به درستی اعمال می‌شود
    public static function grant_feedback_access($caps, $cap, $user_id, $args) {
        if ($cap === 'manage_feedback') {
            $user = get_userdata($user_id);
            if ($user && (in_array('digital_marketer', $user->roles) || in_array('administrator', $user->roles))) {
                $caps = ['exist']; // دسترسی فراهم می‌شود
            } else {
                $caps = ['do_not_allow']; // دسترسی ممنوع
            }
        }
        return $caps;
    }
}

Feedback_Role_Manager::init();
