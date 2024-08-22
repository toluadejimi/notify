<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\controllers;

use Altum\Alerts;
use Altum\Title;

class Subscriber extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $subscriber_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$subscriber = db()->where('subscriber_id', $subscriber_id)->where('user_id', $this->user->user_id)->getOne('subscribers')) {
            redirect('subscribers');
        }

        $subscriber->keys = json_decode($subscriber->keys ?? '');
        $subscriber->custom_parameters = json_decode($subscriber->custom_parameters ?? '', true);

        /* Get the subscribers_logs list for the user */
        $subscriber_logs = db()->where('subscriber_id', $subscriber->subscriber_id)->orderBy('subscriber_log_id', 'DESC')->get('subscribers_logs', 5);

        /* Get the website */
        $website = (new \Altum\Models\Website())->get_website_by_website_id($subscriber->website_id);

        /* Set a custom title */
        Title::set(sprintf(l('subscriber.title'), $subscriber->ip));

        /* Prepare the view */
        $data = [
            'subscriber' => $subscriber,
            'subscriber_logs' => $subscriber_logs,
            'website' => $website,
        ];

        $view = new \Altum\View('subscriber/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
