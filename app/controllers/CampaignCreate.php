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

class CampaignCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.campaigns')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('campaigns');
        }

        /* Check for the plan limit */
        $campaigns_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`pusher_campaigns_current_month`');
        if($this->user->plan_settings->campaigns_per_month_limit != -1 && $campaigns_current_month >= $this->user->plan_settings->campaigns_per_month_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('campaigns');
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

            /* Segment */
            $segment_type = null;
            if(is_numeric($_POST['segment'])) {

                /* Get settings from custom segments */
                $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($_POST['segment']);

                if(!$segment || $_POST['website_id'] != $segment->website_id) {
                    $_POST['segment'] = 'all';
                }

                switch($segment->type) {
                    case 'custom':

                        $segment_type = 'custom';
                        $_POST['subscribers_ids'] = implode(',', $segment->settings->subscribers_ids);

                        break;

                    case 'filter':

                        $segment_type = 'filter';
                        $_POST['filters_continents'] = $segment->settings->filters_continents;
                        $_POST['filters_countries'] = $segment->settings->filters_countries;
                        $_POST['filters_device_type'] = $segment->settings->filters_device_type;

                        break;
                }

            } else {
                $_POST['segment'] = in_array($_POST['segment'], ['all', 'custom', 'filter']) ? input_clean($_POST['segment']) : 'all';
                $segment_type = $_POST['segment'];
            }

            /* Scheduling */
            $_POST['is_scheduled'] = (int) isset($_POST['is_scheduled']);
            $_POST['scheduled_datetime'] = $_POST['is_scheduled'] && !empty($_POST['scheduled_datetime']) && Date::validate($_POST['scheduled_datetime'], 'Y-m-d H:i:s') ?
                (new \DateTime($_POST['scheduled_datetime'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s')
                : \Altum\Date::$date;

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
            $required_fields = ['name', 'title', 'description'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $image = \Altum\Uploads::process_upload(null, 'websites_campaigns_images', 'image', 'image_remove', settings()->websites->campaigns_images_limit);

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = [
                    /* Scheduling */
                    'is_scheduled' => $_POST['is_scheduled'],

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

                /* Get all the users needed */
                switch($segment_type) {
                    case 'all':
                        $subscribers = db()->where('user_id', $this->user->user_id)->where('website_id', $_POST['website_id'])->get('subscribers', null, ['subscriber_id', 'user_id']);
                        break;

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

                $status = $_POST['scheduled_datetime'] ? 'scheduled' : 'processing';
                if(isset($_POST['save'])) {
                    $status = 'draft';
                }

                /* Database query */
                $campaign_id = db()->insert('campaigns', [
                    'website_id' => $_POST['website_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'url' => $_POST['url'],
                    'image' => $image,
                    'segment' => $_POST['segment'],
                    'settings' => json_encode($settings),
                    'subscribers_ids' => json_encode($subscribers_ids),
                    'sent_subscribers_ids' => '[]',
                    'total_push_notifications' => count($subscribers_ids),
                    'status' => $status,
                    'scheduled_datetime' => $_POST['scheduled_datetime'],
                    'datetime' => \Altum\Date::$date,
                ]);

                /* Database query */
                db()->where('user_id', $this->user->user_id)->update('users', [
                    'pusher_campaigns_current_month' => db()->inc()
                ]);

                if(isset($_POST['save'])) {
                    /* Set a nice success message */
                    Alerts::add_success(sprintf(l('campaigns.success_message.save'), '<strong>' . $_POST['name'] . '</strong>'));
                } else {
                    /* Update the total website sent campaigns */
                    db()->where('website_id', $_POST['website_id'])->update('websites', [
                        'total_sent_campaigns' => db()->inc()
                    ]);

                    /* Set a nice success message */
                    if($_POST['is_scheduled']) {
                        Alerts::add_success(sprintf(l('campaigns.success_message.scheduled'), '<strong>' . $_POST['name'] . '</strong>', '<strong>' . \Altum\Date::get_time_until($_POST['scheduled_datetime']) . '</strong>'));
                    } else {
                        Alerts::add_success(sprintf(l('campaigns.success_message.send'), '<strong>' . $_POST['name'] . '</strong>'));
                    }
                }

                /* Clear the cache */
                cache()->deleteItem('campaigns?user_id=' . $this->user->user_id);
                cache()->deleteItem('campaigns_total?user_id=' . $this->user->user_id);
                cache()->deleteItem('campaigns_dashboard?user_id=' . $this->user->user_id);

                redirect('campaigns');
            }

        }

        /* Single push notification */
        if(isset($_GET['subscriber_id']) && empty($_POST)) {
            $_POST['segment'] = 'custom';
            $_POST['subscribers_ids'] = (int) $_GET['subscriber_id'];
        }

        $values = [
            'website_id' => $_POST['website_id'] ?? $_GET['website_id'] ?? array_key_first($websites),
            'name' => $_POST['name'] ?? null,
            'title' => $_POST['title'] ?? null,
            'description' => $_POST['description'] ?? null,
            'url' => $_POST['url'] ?? null,
            'is_scheduled' => $_POST['is_scheduled'] ?? null,
            'scheduled_datetime' => $_POST['scheduled_datetime'] ?? null,
            'ttl' => $_POST['ttl'] ?? array_key_last($notifications_ttl),
            'is_silent' => $_POST['is_silent'] ?? null,
            'is_auto_hide' => $_POST['is_auto_hide'] ?? null,
            'urgency' => $_POST['urgency'] ?? 'normal',
            'segment' => $_POST['segment'] ?? 'all',
            'subscribers_ids' => $_POST['subscribers_ids'] ?? null,
            'filters_device_type' => $_POST['filters_device_type'] ?? [],
            'filters_continents' => $_POST['filters_continents'] ?? [],
            'filters_countries' => $_POST['filters_countries'] ?? [],
            'filters_cities' => implode(',', $_POST['filters_cities'] ?? []),
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
            'websites' => $websites,
            'segments' => $segments,
            'notifications_ttl' => $notifications_ttl,
        ];

        $view = new \Altum\View('campaign-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
