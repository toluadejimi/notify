<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\controllers;

use Altum\Title;

class Flow extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $flow_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$flow = db()->where('flow_id', $flow_id)->where('user_id', $this->user->user_id)->getOne('flows')) {
            redirect('flows');
        }

        $flow->settings = json_decode($flow->settings ?? '');

        /* Get the subscribers_logs list for the user */
        $subscribers_logs = db()->where('flow_id', $flow->flow_id)->orderBy('subscriber_log_id', 'DESC')->get('subscribers_logs', 5);

        /* Get the website */
        $website = (new \Altum\Models\Website())->get_website_by_website_id($flow->website_id);

        /* TTL */
        $notifications_ttl = require APP_PATH . 'includes/notifications_ttl.php';

        /* Set a custom title */
        Title::set(sprintf(l('flow.title'), $flow->name));

        /* Prepare the view */
        $data = [
            'flow' => $flow,
            'notifications_ttl' => $notifications_ttl,
            'subscriber_logs' => $subscribers_logs,
            'website' => $website,
        ];

        $view = new \Altum\View('flow/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
