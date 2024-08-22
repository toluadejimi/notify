<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Models;

class NotificationHandlers extends Model {

    public function get_notification_handlers_by_user_id($user_id) {

        /* Get the user notification handlers */
        $notification_handlers = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('notification_handlers?user_id=' . $user_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $notification_handlers_result = database()->query("SELECT * FROM `notification_handlers` WHERE `user_id` = {$user_id}");
            while($row = $notification_handlers_result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');
                $notification_handlers[$row->notification_handler_id] = $row;
            }

            cache()->save(
                $cache_instance->set($notification_handlers)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id)
            );

        } else {

            /* Get cache */
            $notification_handlers = $cache_instance->get();

        }

        return $notification_handlers;

    }

}
