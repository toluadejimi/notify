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
use Altum\Date;

class FlowUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.flows')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('flows');
        }

        $flow_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$flow = db()->where('flow_id', $flow_id)->where('user_id', $this->user->user_id)->getOne('flows')) {
            redirect('flows');
        }

        $flow->settings = json_decode($flow->settings ?? '');

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        /* Get available segments */
        $segments = (new \Altum\Models\Segment())->get_segments_by_user_id($this->user->user_id);

        /* TTL */
        $notifications_ttl = require APP_PATH . 'includes/notifications_ttl.php';

        if(!empty($_POST)) {
            /* Filter some the variables */
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['title'] = input_clean($_POST['title'], 64);
            $_POST['description'] = input_clean($_POST['description'], 128);
            $_POST['url'] = filter_var(input_clean($_POST['url'], 512), FILTER_SANITIZE_URL);
            $_POST['website_id'] = array_key_exists($_POST['website_id'], $websites) ? (int) $_POST['website_id'] : array_key_first($websites);
            $_POST['wait_time'] = (int) $_POST['wait_time'];
            $_POST['wait_time_type'] = isset($_POST['wait_time_type']) && in_array($_POST['wait_time_type'], ['minutes', 'hours', 'days']) ? $_POST['wait_time_type'] : 'days';

            if($_POST['wait_time'] < 1) $_POST['wait_time'] = 1;

            /* Max is 90 days of ahead scheduling */
            switch ($_POST['wait_time_type']) {
                case 'minutes':
                    if($_POST['wait_time'] > 129600) $_POST['wait_time'] = 129600;
                    break;

                case 'hours':
                    if($_POST['wait_time'] > 2160) $_POST['wait_time'] = 2160;
                    break;

                case 'days':
                    if($_POST['wait_time'] > 90) $_POST['wait_time'] = 90;
                    break;
            }

            /* Segment */
            if(is_numeric($_POST['segment'])) {

                /* Get settings from custom segments */
                $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($_POST['segment']);

                if(!$segment || $_POST['website_id'] != $segment->website_id) {
                    $_POST['segment'] = 'all';
                }

            } else {
                $_POST['segment'] = in_array($_POST['segment'], ['all', 'custom', 'filter']) ? input_clean($_POST['segment']) : 'all';
            }

            /* Advanced */
            $_POST['ttl'] = array_key_exists($_POST['ttl'], $notifications_ttl) ? (int) $_POST['ttl'] : array_key_last($notifications_ttl);
            $_POST['urgency'] = isset($_POST['urgency']) && in_array($_POST['urgency'], ['low', 'normal', 'high']) ? $_POST['urgency'] : 'normal';
            $_POST['is_silent'] = (int) isset($_POST['is_silent']);
            $_POST['is_auto_hide'] = (int) isset($_POST['is_auto_hide']);

            /* Buttons */
            $_POST['button_title_1'] = input_clean($_POST['button_title_1'], 16);
            $_POST['button_url_1'] = filter_var(input_clean($_POST['button_url_1'], 512), FILTER_SANITIZE_URL);
            $_POST['button_title_2'] = input_clean($_POST['button_title_2'], 16);
            $_POST['button_url_2'] = filter_var(input_clean($_POST['button_url_2'], 512), FILTER_SANITIZE_URL);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'title', 'description'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $image = \Altum\Uploads::process_upload($flow->image, 'websites_flows_images', 'image', 'image_remove', settings()->websites->flows_images_limit);

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = [
                    /* Advanced */
                    'ttl' => $_POST['ttl'],
                    'urgency' => $_POST['urgency'],
                    'is_silent' => $_POST['is_silent'],
                    'is_auto_hide' => $_POST['is_auto_hide'],

                    /* Buttons */
                    'button_title_1' => $_POST['button_title_1'],
                    'button_url_1' => $_POST['button_url_1'],
                    'button_title_2' => $_POST['button_title_2'],
                    'button_url_2' => $_POST['button_url_2'],
                ];

                /* Database query */
                db()->where('flow_id', $flow->flow_id)->update('flows', [
                    'website_id' => $_POST['website_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'url' => $_POST['url'],
                    'image' => $image,
                    'segment' => $_POST['segment'],
                    'settings' => json_encode($settings),
                    'wait_time' => $_POST['wait_time'],
                    'wait_time_type' => $_POST['wait_time_type'],
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('flow?flow_id=' . $flow->flow_id);
                cache()->deleteItem('flows?user_id=' . $flow->user_id);
                cache()->deleteItem('flows?website_id=' . $flow->website_id);

                /* Refresh the page */
                redirect('flow-update/' . $flow_id);
            }
        }

        /* Prepare the view */
        $data = [
            'flow' => $flow,
            'websites' => $websites,
            'segments' => $segments,
            'notifications_ttl' => $notifications_ttl,
        ];

        $view = new \Altum\View('flow-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
