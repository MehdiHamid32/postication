<?php

/**
 * Plugin Name: Postication
 * Description: Send Creating, Updating And Deleting Post Notification To Telegram Channel
 * Version: 1.0.0
 * Author: Mehdi Hamid
 * Author URI: https://github.com/MehdiHamid32/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare( strict_types = 1 );


register_activation_hook(__FILE__, function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'postication_telegram_messages';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        chat_id varchar(255) NOT NULL,
        message_id varchar(255) NOT NULL,
        sent_at datetime NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});


require_once __DIR__ . '/BotSettings.php';
require_once __DIR__ . '/SendNotification.php';

new BotSettings();
new SendNotification();

