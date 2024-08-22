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

class Website extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $website_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites')) {
            redirect('websites');
        }

        $website->settings = json_decode($website->settings ?? '');
        $website->keys = json_decode($website->keys ?? '');

        /* Get the campaigns list for the website */
        $campaigns = db()->where('website_id', $website->website_id)->orderBy('campaign_id', 'DESC')->get('campaigns', 5);
        foreach($campaigns as $row) $row->settings = json_decode($row->settings ?? '');

        /* Get the subscribers list for the website */
        $subscribers = db()->where('website_id', $website->website_id)->orderBy('subscriber_id', 'DESC')->get('subscribers', 5);

        /* Get the subscribers_logs list for the website */
        $subscribers_logs = db()->where('website_id', $website->website_id)->orderBy('subscriber_log_id', 'DESC')->get('subscribers_logs', 5);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Set a custom title */
        Title::set(sprintf(l('website.title'), $website->name));

        /* Prepare the view */
        $data = [
            'domains' => $domains,
            'website' => $website,
            'campaigns' => $campaigns,
            'subscribers' => $subscribers,
            'subscriber_logs' => $subscribers_logs,
        ];

        $view = new \Altum\View('website/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
