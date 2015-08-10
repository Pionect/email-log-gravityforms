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
    const VERSION                  = '0.2';
    // JS Stuff
    const JS_HANDLE                = 'email-log-gravityforms';

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
        
        // Register hooks
        add_action( 'admin_menu', array( &$this, 'register_settings_page' ));

        add_action('phpmailer_init', array(&$this, 'enable_debug_phpmailer'));
        add_filter('gform_pre_send_email', array(&$this, 'start_email_output_buffering'), 10, 1);
        add_action('gform_after_email', array(&$this, 'end_email_output_buffering'), 10, 5);
        
        add_action('wp_ajax_show_smtp_response', array(&$this, 'display_smtp_response_callback'));
    }
    
    /**
     * Register the settings page
     */
    function register_settings_page() {
        // enqueue JavaScript
        add_action( 'admin_print_scripts', array( &$this, 'include_js' ) );
    }
    
    /**
     * Include JavaScript displaying email response.
     *
     * @since 0.2
     */
    function include_js() {
        wp_enqueue_script( self::JS_HANDLE, plugins_url( '/js/email-log-grafityforms.js', __FILE__ ), array( 'jquery' ), self::VERSION, TRUE );
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

        if ($column_name == 'smtp_response') {
            $response = $item->smtp_response;
            echo ( strlen($response) > 0 ? substr($response,0,60).'... [<a href="#" class="email_response" id="email_response_'.$item->id.'">View Response</a>]'   : 'N/A' );
        }
    }
    
    /**
     * AJAX callback for displaying email SMTP response
     *
     * @since 0.2
     */
    function display_smtp_response_callback(){
        global $wpdb;
        
        $email_id   = absint( $_POST['email_id'] );
        
        $table_name = $wpdb->prefix . Email_Log_Gravityforms::TABLE_NAME;
        
        // Select the matching item from the database
        $query      = $wpdb->prepare( "SELECT smtp_response FROM " . $table_name . " WHERE id = %d", $email_id );
        $content    = $wpdb->get_results( $query );
        
        // Write the full response to the window
        echo $content[0]->smtp_response;
       
        die(); // this is required to return a proper result
    }
    
}

new Email_Log_Gravityforms();
