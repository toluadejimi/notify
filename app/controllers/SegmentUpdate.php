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

class SegmentUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.segments')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('segments');
        }

        $segment_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$segment = db()->where('segment_id', $segment_id)->where('user_id', $this->user->user_id)->getOne('segments')) {
            redirect('segments');
        }

        $segment->settings = json_decode($segment->settings ?? '');

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            /* Filter some the variables */
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['website_id'] = array_key_exists($_POST['website_id'], $websites) ? (int) $_POST['website_id'] : array_key_first($websites);
            $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['custom', 'filter']) ? $_POST['type'] : 'filter';

            $_POST['subscribers_ids'] = trim($_POST['subscribers_ids'] ?? '');
            if($_POST['subscribers_ids']) {
                $_POST['subscribers_ids'] = explode(',', $_POST['subscribers_ids'] ?? '');
                if(count($_POST['subscribers_ids'])) {
                    $_POST['subscribers_ids'] = array_map(function ($user_id) {
                        return (int) $user_id;
                    }, $_POST['subscribers_ids']);
                    $_POST['subscribers_ids'] = array_unique($_POST['subscribers_ids']);
                }
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $settings = [];

                /* Get all the users needed */
                switch($_POST['type']) {

                    case 'custom':
                        $subscribers = db()->where('user_id', $this->user->user_id)->where('website_id', $_POST['website_id'])->where('subscriber_id', $_POST['subscribers_ids'], 'IN')->get('subscribers', null, ['subscriber_id']);
                        break;

                    case 'filter':

                        $query = db()->where('user_id', $this->user->user_id)->where('website_id', $_POST['website_id']);

                        $has_filters = false;

                        /* Cities */
                        if($_POST['filters_cities']) {
                            $_POST['filters_cities'] = explode(',', $_POST['filters_cities']);

                            if(count($_POST['filters_cities'])) {
                                $_POST['filters_cities'] = array_map(function($city) {
                                    return query_clean($city);
                                }, $_POST['filters_cities']);
                                $_POST['filters_cities'] = array_unique($_POST['filters_cities']);

                                $has_filters = true;
                                $query->where('city_name', $_POST['filters_cities'], 'IN');
                                $settings['filters_cities'] = $_POST['filters_cities'];
                            }
                        }

                        /* Countries */
                        if(isset($_POST['filters_countries'])) {
                            $_POST['filters_countries'] = array_filter($_POST['filters_countries'] ?? [], function($country) {
                                return array_key_exists($country, get_countries_array());
                            });

                            $has_filters = true;
                            $query->where('country_code', $_POST['filters_countries'], 'IN');
                            $settings['filters_countries'] = $_POST['filters_countries'];
                        }

                        /* Continents */
                        if(isset($_POST['filters_continents'])) {
                            $_POST['filters_continents'] = array_filter($_POST['filters_continents'] ?? [], function($country) {
                                return array_key_exists($country, get_continents_array());
                            });

                            $has_filters = true;
                            $query->where('continent_code', $_POST['filters_continents'], 'IN');
                            $settings['filters_continents'] = $_POST['filters_continents'];
                        }

                        /* Device type */
                        if(isset($_POST['filters_device_type'])) {
                            $_POST['filters_device_type'] = array_filter($_POST['filters_device_type'] ?? [], function($device_type) {
                                return in_array($device_type, ['desktop', 'tablet', 'mobile']);
                            });

                            $has_filters = true;
                            $query->where('device_type', $_POST['filters_device_type'], 'IN');
                            $settings['filters_device_type'] = $_POST['filters_device_type'];
                        }

                        /* Languages */
                        if(isset($_POST['filters_languages'])) {
                            $_POST['filters_languages'] = array_filter($_POST['filters_languages'], function($locale) {
                                return array_key_exists($locale, get_locale_languages_array());
                            });

                            $has_filters = true;
                            $query->where('browser_language', $_POST['filters_languages'], 'IN');
                            $settings['filters_languages'] = $_POST['filters_languages'];
                        }

                        /* Filters operating systems */
                        if (isset($_POST['filters_operating_systems'])) {
                            $_POST['filters_operating_systems'] = array_filter($_POST['filters_operating_systems'], function($os_name) {
                                return in_array($os_name, ['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS']);
                            });

                            $has_filters = true;
                            $query->where('os_name', $_POST['filters_operating_systems'], 'IN');
                            $settings['filters_operating_systems'] = $_POST['filters_operating_systems'];
                        }

                        /* Filters browsers */
                        if (isset($_POST['filters_browsers'])) {
                            $_POST['filters_browsers'] = array_filter($_POST['filters_browsers'], function($browser_name) {
                                return in_array($browser_name, ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet']);
                            });

                            $has_filters = true;
                            $query->where('browser_name', $_POST['filters_browsers'], 'IN');
                            $settings['filters_browsers'] = $_POST['filters_browsers'];
                        }

                        $subscribers = $has_filters ? $query->get('subscribers', null, ['subscriber_id']) : [];

                        db()->reset();

                        break;
                }

                $subscribers_ids = [];
                foreach($subscribers as $push_subscriber) {
                    $subscribers_ids[] = $push_subscriber->subscriber_id;
                }

                $settings['subscribers_ids'] = $_POST['type'] == 'custom' ? $subscribers_ids : [];

                /* Database query */
                db()->where('segment_id', $segment->segment_id)->update('segments', [
                    'website_id' => $_POST['website_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'settings' => json_encode($settings),
                    'total_subscribers' => count($subscribers),
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('segment?segment_id=' . $segment->segment_id);

                /* Refresh the page */
                redirect('segment-update/' . $segment_id);
            }
        }

        /* Prepare the view */
        $data = [
            'segment' => $segment,
            'websites' => $websites,
        ];

        $view = new \Altum\View('segment-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
