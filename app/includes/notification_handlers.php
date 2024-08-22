<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

$enabled_notification_handlers = [];

foreach(require APP_PATH . 'includes/available_notification_handlers.php' as $type => $notification_handler) {
    if(settings()->notification_handlers->{$type . '_is_enabled'}) {
        $enabled_notification_handlers[$type] = $notification_handler;
    }
}

return $enabled_notification_handlers;
