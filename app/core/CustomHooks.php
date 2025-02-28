<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum;

class CustomHooks {

    public static function user_finished_registration($data = []) {
        $plan_settings = json_decode($data['plan_settings']);

        if($plan_settings->notification_handlers_email_limit > 0) {
            db()->insert('notification_handlers', [
                'user_id' => $data['user_id'],
                'type' => 'email',
                'name' => $data['email'],
                'settings' => json_encode([
                    'email' => $data['email']
                ]),
                'datetime' => \Altum\Date::$date,
            ]);
        }
    }

}
