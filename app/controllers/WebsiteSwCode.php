<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Controllers;

class WebsiteSwCode extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $website_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        $website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites');

        /* Get the server monitor */
        if(!$website) {
            redirect('not-found');
        }

        $sw_js_url = SITE_URL . 'pixel_service_worker.js';

        $content = <<<ALTUM
let website_id = $website_id;
let website_pixel_key = '$website->pixel_key';
importScripts("$sw_js_url");
ALTUM;

        /* Prepare headers */
        header('Content-Description: File Transfer');
        header('Content-Type: text/javascript');
        header('Content-Disposition: attachment; filename="' . settings()->websites->service_worker_file_name . '.js"');
        header('Content-Length: ' . mb_strlen($content));

        /* Output data */
        echo $content;
    }

}
