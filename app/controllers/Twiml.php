<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\controllers;

class Twiml extends Controller {

    public function index() {

        if(!settings()->notification_handlers->twilio_call_is_enabled) {
            redirect();
        }

        $language_key = isset($this->params[0]) ? str_replace('-', '_', input_clean($this->params[0])) : null;

        if(!$language_key) {
            redirect();
        }

        $available_language_keys = [
            'websites.simple_notification',
        ];

        if(!in_array($language_key, $available_language_keys)) {
            redirect();
        }

        /* Process parameters */
        $parameters = [];
        foreach($_GET as $key => $value) {
            if(string_starts_with('param', $key)) {
                $parameters[] = input_clean($value);
            }
        }

        header('Content-Type: text/xml');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<Response>';
        echo '<Say>' . sprintf(l($language_key), ...$parameters) . '</Say>';
        echo '</Response>';

        die();
    }

}
