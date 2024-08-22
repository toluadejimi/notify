<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Controllers;

use Altum\Models\Plan;

class AdminUserView extends Controller {

    public function index() {

        $user_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        /* Check if user exists */
        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('admin/users');
        }

        /* Get widget stats */
        $websites = db()->where('user_id', $user_id)->getValue('websites', 'count(`website_id`)');
        $campaigns = db()->where('user_id', $user_id)->getValue('campaigns', 'count(`campaign_id`)');
        $subscribers = db()->where('user_id', $user_id)->getValue('websites', 'sum(`total_subscribers`)');
        $segments = db()->where('user_id', $user_id)->getValue('segments', 'count(`segment_id`)');
        $flows = db()->where('user_id', $user_id)->getValue('flows', 'count(`flow_id`)');
        $total_sent_push_notifications = db()->where('user_id', $user_id)->getValue('websites', 'SUM(total_sent_push_notifications)');
        $domains = db()->where('user_id', $user_id)->getValue('domains', 'count(`domain_id`)');
        $payments = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('user_id', $user_id)->getValue('payments', 'count(`id`)') : 0;
        $notification_handlers = db()->where('user_id', $user_id)->getValue('notification_handlers', 'count(`notification_handler_id`)');

        /* Get the current plan details */
        $user->plan = (new Plan())->get_plan_by_id($user->plan_id);

        /* Check if its a custom plan */
        if($user->plan_id == 'custom') {
            $user->plan->settings = $user->plan_settings;
        }

        $user->billing = json_decode($user->billing);

        /* Main View */
        $data = [
            'user' => $user,
            'websites' => $websites,
            'campaigns' => $campaigns,
            'subscribers' => $subscribers,
            'segments' => $segments,
            'flows' => $flows,
            'total_sent_push_notifications' => $total_sent_push_notifications,
            'domains' => $domains,
            'payments' => $payments,
            'notification_handlers' => $notification_handlers,
        ];

        $view = new \Altum\View('admin/user-view/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
