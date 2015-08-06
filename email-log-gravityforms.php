<?php

/**
  Plugin Name: Email Log - Gravityforms
  Description: An add-on Plugin to Email Log Plugin, that allows you to see the SMTP response
  Version: 0.1
  Author: Pionect
  Author URI: http://www.pionect.nl
  Text Domain: email-log
 */
class Email_Log_Gravityforms {

    function __construct() {
        
        // column hooks
        if (is_admin()) {
            add_filter(EmailLog::HOOK_LOG_COLUMNS, array(&$this, 'add_new_smtp_response_column'));
            add_action(EmailLog::HOOK_LOG_DISPLAY_COLUMNS, array(&$this, 'display_new_smtp_response_column'), 10, 2);
        }

        add_action('phpmailer_init', array(&$this, 'enable_debug_phpmailer'));
        add_filter('gform_pre_send_email', array(&$this, 'start_email_output_buffering'), 10, 1);
        add_action('gform_after_email', array(&$this, 'end_email_output_buffering'), 10, 5);
    }

    function enable_debug_phpmailer($phpmailer) {
        $phpmailer->SMTPDebug = true;
    }

    function start_email_output_buffering($email) {
        $email['headers']['PNCT-TOKEN'] = "PNCT-TOKEN: '" . uniqid() . "'";

        ob_start();

        return $email;
    }

    function end_email_output_buffering($is_success, $to, $subject, $message, $headers) {
        
        if (array_key_exists('PNCT-TOKEN', $headers) == FALSE) {
            return;
        }

        $smtp_debug = ob_get_clean();
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

        $header = self::parse_header($item->headers);

        if ($column_name == 'smtp_response') {
            echo ( isset($header['smtp_response']) ? esc_attr($header['smtp_response']) : 'N/A' );
        }
    }

}

// Start this plugin once all other plugins are fully loaded
function Email_Log_Gravityforms() {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if (is_plugin_active('email-log/email-log.php')) {
        global $EmailLogSmtpResponse;
        $EmailLogSmtpResponse = new Email_Log_Gravityforms();
    }
}

add_action('init', 'Email_Log_Gravityforms', 100);
