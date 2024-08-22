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
use MaxMind\Db\Reader;

class FlowCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.flows')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('flows');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `flows` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->flows_limit != -1 && $total_rows >= $this->user->plan_settings->flows_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('flows');
        }

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
                $_POST['segment'] = in_array($_POST['segment'], ['all']) ? input_clean($_POST['segment']) : 'all';
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

            $image = \Altum\Uploads::process_upload(null, 'websites_flows_images', 'image', 'image_remove', settings()->websites->flows_images_limit);

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
                $flow_id = db()->insert('flows', [
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
                    'datetime' => \Altum\Date::$date,
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('flows?user_id=' . $this->user->user_id);
                cache()->deleteItem('flows?website_id=' . $_POST['website_id']);

                redirect('flows');
            }

        }

        $values = [
            'website_id' => $_POST['website_id'] ?? $_GET['website_id'] ?? array_key_first($websites),
            'name' => $_POST['name'] ?? null,
            'wait_time' => $_POST['wait_time'] ?? 1,
            'wait_time_type' => $_POST['wait_time'] ?? 'days',
            'title' => $_POST['title'] ?? null,
            'description' => $_POST['description'] ?? null,
            'url' => $_POST['url'] ?? null,
            'ttl' => $_POST['ttl'] ?? array_key_last($notifications_ttl),
            'is_silent' => $_POST['is_silent'] ?? null,
            'is_auto_hide' => $_POST['is_auto_hide'] ?? null,
            'urgency' => $_POST['urgency'] ?? 'normal',
            'segment' => $_POST['segment'] ?? 'all',
            'subscribers_ids' => $_POST['subscribers_ids'] ?? null,
            'filters_device_type' => $_POST['filters_device_type'] ?? [],
            'filters_continents' => $_POST['filters_continents'] ?? [],
            'filters_countries' => $_POST['filters_countries'] ?? [],
            'filters_operating_systems' => $_POST['filters_operating_systems'] ?? [],
            'filters_browsers' => $_POST['filters_browsers'] ?? [],
            'filters_languages' => $_POST['filters_languages'] ?? [],
            'button_title_1' => $_POST['button_title_1'] ?? null,
            'button_url_1' => $_POST['button_url_1'] ?? null,
            'button_title_2' => $_POST['button_title_2'] ?? null,
            'button_url_2' => $_POST['button_url_2'] ?? null,
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'notifications_ttl' => $notifications_ttl,
            'websites' => $websites,
            'segments' => $segments,
        ];

        $view = new \Altum\View('flow-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
