<?php

/**
  Plugin Name: Email Log - Gravity Forms
  Description: An add-on Plugin to Email Log Plugin, that allows you to see the SMTP response
  Version: 0.2
  Author: Pionect
  Author URI: http://www.pionect.nl
  Text Domain: email-log
 */

if ( ! defined( 'EMAIL_LOG_GRAVITYFORMS_PLUGIN_FILE' ) ) {
    define( 'EMAIL_LOG_GRAVITYFORMS_PLUGIN_FILE', __FILE__ );
}

// handle update email_log table update
require_once dirname( __FILE__ ) . '/include/update.php';

class Email_Log_Gravityforms {
    
    const TABLE_NAME               = 'email_log';          /* Database table name */

    function __construct() {
        add_action('init', array($this,'init'), 100);
    }
    
    function init(){
        if (is_plugin_active('email-log/email-log.php') == FALSE) {
            // show error that this plugins needs 'Email Log' 
            return;
        }
        
        // column hooks
        if (is_admin()) {
            add_filter(EmailLog::HOOK_LOG_COLUMNS, array(&$this, 'add_new_smtp_response_column'));
            add_action(EmailLog::HOOK_LOG_DISPLAY_COLUMNS, array(&$this, 'display_new_smtp_response_column'), 10, 2);
        }

        add_action('phpmailer_init', array(&$this, 'enable_debug_phpmailer'));
        add_filter('gform_pre_send_email', array(&$this, 'start_email_output_buffering'), 10, 1);
        add_action('gform_after_email', array(&$this, 'end_email_output_buffering'), 10, 5);
    }

    /**
     * Enable debug for phpmailer
     */
    function enable_debug_phpmailer($phpmailer) {
        $phpmailer->SMTPDebug = 2;
    }
    
    /**
     * Set a random uniqid to headers of the email
     */
    function start_email_output_buffering($email) {
        $email['headers']['PNCT-TOKEN'] = "PNCT-TOKEN: " . uniqid() . "";

        ob_start();

        return $email;
    }
    
    /**
     * Update the wp_email_log.smtp_response column with the response. 
     */
    function end_email_output_buffering($is_success, $to, $subject, $message, $headers) {
        global $wpdb;
        
        if (array_key_exists('PNCT-TOKEN', $headers) == FALSE) {
            return;
        }
        
        $smtp_debug = ob_get_clean();
        
        $table_name = $wpdb->prefix . Email_Log_Gravityforms::TABLE_NAME;
        
        $sql = "UPDATE `" . $table_name . "` SET `smtp_response` = '". $smtp_debug ."'"
                . "WHERE `headers` LIKE '%" . $headers['PNCT-TOKEN'] . "%' ;";
        
        $wpdb->query($sql);
        
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

new Email_Log_Gravityforms();
