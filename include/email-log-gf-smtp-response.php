<?php

class Email_Log_Gf_Smtp_Response {
    
    function __construct(){ 
       // Register hooks
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
    
}

