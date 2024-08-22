<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\controllers;

use Altum\Models\User;
use MaxMind\Db\Reader;

class PixelServiceWorker extends Controller {

    public function index() {
        $seconds_to_cache = settings()->websites->pixel_cache;
        header('Content-Type: application/javascript');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $seconds_to_cache) . ' GMT');
        header('Pragma: cache');
        header('Cache-Control: max-age=' . $seconds_to_cache);

        /* Main View */
        $data = [];

        $view = new \Altum\View('pixel-service-worker/index', (array) $this);

        $view_data = $view->run($data);

        /* Remove <script> tags */
        $view_data = str_replace('<script>', '', $view_data);
        $view_data = str_replace('</script>', '', $view_data);

        echo $view_data;
    }

}
