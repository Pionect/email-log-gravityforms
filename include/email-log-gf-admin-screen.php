<?php

class Email_Log_Gf_Admin_Screen {

    function __construct() {
        // column hooks
        if (is_admin()) {
            add_filter(EmailLog::HOOK_LOG_COLUMNS, array(&$this, 'add_new_smtp_response_column'));
            add_action(EmailLog::HOOK_LOG_DISPLAY_COLUMNS, array(&$this, 'display_new_smtp_response_column'), 10, 2);
            add_action('admin_enqueue_scripts', array(&$this, 'include_js'));
            add_action('wp_ajax_show_smtp_response', array(&$this, 'display_smtp_response_callback'));
        }
    }

    /**
     * Include JavaScript displaying email response.
     *
     * @since 0.2
     */
    function include_js() {
        wp_enqueue_script(EmailLog::JS_HANDLE, plugins_url('/js/email-log-grafityforms.js', __FILE__), array('jquery'), EmailLog::VERSION, TRUE);
    }

    /**
     * Add new SMTP Response column
     */
    function add_new_smtp_response_column($column) {
        $column['smtp_response'] = __('SMTP Response', 'email-log');

        return $column;
    }

    /**
     * Display content for SMTP column
     */
    function display_new_smtp_response_column($column_name, $item) {

        if ($column_name == 'smtp_response') {
            $response = $item->smtp_response;
            echo ( strlen($response) > 0 ? substr($response, 0, 60) . '... [<a href="#" class="email_response" data-email_id="' . $item->id . '">View Response</a>]' : 'N/A' );
        }
    }

    /**
     * AJAX callback for displaying email SMTP response
     *
     * @since 0.2
     */
    function display_smtp_response_callback() {
        global $wpdb;

        $email_id = $_POST['email_id'];

        $table_name = $wpdb->prefix . Email_Log_Gravityforms::TABLE_NAME;

        // Select the matching item from the database
        $query   = $wpdb->prepare("SELECT smtp_response FROM " . $table_name . " WHERE id = %d", $email_id);
        $content = $wpdb->get_var($query);

        // Write the full response to the window
        echo $content;

        die(); // this is required to return a proper result
    }

}
