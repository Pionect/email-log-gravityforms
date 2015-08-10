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

class Email_Log_Gravityforms {
    
    const TABLE_NAME               = 'email_log';          /* Database table name */
    const VERSION                  = '0.2';
    // JS Stuff
    const JS_HANDLE                = 'email-log-gravityforms';

    function __construct() {
        
        // handle update email_log table update
        require_once dirname( __FILE__ ) . '/include/update.php';
        
        add_action('init', array($this,'init'), 100);
    }
    
    function init(){
        if (is_plugin_active('email-log/email-log.php') == FALSE) {
            // show error that this plugins needs 'Email Log' 
            return;
        }
        
        include 'include/email-log-gf-admin-screen.php';
        include 'include/email-log-gf-smtp-response.php';
        
        new Email_Log_Gf_Smtp_Response();
        new Email_Log_Gf_Admin_Screen();
        
    }
    
}

new Email_Log_Gravityforms();
