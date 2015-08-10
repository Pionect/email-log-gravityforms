<?php

/**
 * Db email_log table update
 *
 * @package     Email Log - Gravity Forms
 * @subpackage  Update
 * @author      Pionect
 * @since       0.2
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Helper class to update the email_log table
 *
 * @author Pionect
 */
class Email_Log_Table_Update {

    /**
     * Update email_log table
     *
     * @since  0.2
     * @static
     * @access private
     *
     * @global object $wpdb
     */
    public static function update_emaillog_table() {

        global $wpdb;
        $table_name = $wpdb->prefix . Email_Log_Gravityforms::TABLE_NAME;

        $sql_exist_colum = $wpdb->get_results("SELECT * 
                            FROM information_schema.COLUMNS 
                            WHERE TABLE_NAME = '" . $table_name . "' 
                            AND COLUMN_NAME = 'smtp_response'");

        if ($sql_exist_colum != "") {

            $sql = "ALTER TABLE `" . $table_name . "` 
                ADD COLUMN `smtp_response` text AFTER `headers`;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $wpdb->query($sql);
        }
    }

}

// When the Plugin installed
register_activation_hook(EMAIL_LOG_GRAVITYFORMS_PLUGIN_FILE, array('Email_Log_Table_Update', 'update_emaillog_table'));
