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
use Altum\Models\Campaign;
use Altum\Response;

class Segments extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'type'], ['name',], ['name', 'datetime', 'last_datetime', 'total_subscribers']));
        $filters->set_default_order_by('segment_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `segments` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('segments?' . $filters->get_get() . '&page=%d')));

        /* Get the segments list for the user */
        $segments = [];
        $segments_result = database()->query("SELECT * FROM `segments` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $segments_result->fetch_object()) {
            $segments[] = $row;
        }

        /* Export handler */
        process_export_json($segments, 'include', ['segment_id', 'user_id', 'name', 'type', 'total_subscribers', 'settings', 'datetime', 'last_datetime',]);
        process_export_csv($segments, 'include', ['segment_id', 'user_id', 'name', 'type', 'total_subscribers', 'datetime', 'last_datetime',]);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'segments' => $segments,
            'websites' => $websites,
            'total_segments' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('segments/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_segment_count() {

        if(!empty($_POST)) {
            redirect();
        }

        \Altum\Authentication::guard();

        $type = isset($_GET['type']) ? input_clean($_GET['type']) : 'all';
        $website_id = isset($_GET['website_id']) ? (int) $_GET['website_id'] : null;

        /* Get the website */
        $website = (new \Altum\Models\Website())->get_website_by_website_id($website_id);

        if(!$website || $website->user_id != $this->user->user_id) {
            Response::json('', 'success', ['count' => 0]);
        }

        /* Get settings from custom segments */
        if(is_numeric($type)) {
            $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($_GET['type']);

            if(!$segment || $website->website_id != $segment->website_id) {
                Response::json('', 'success', ['count' => 0]);
            }

            $type = $segment->type;

            /* Set the custom filters of the custom segment for processing */
            switch($type) {
                case 'custom':
                    $_GET['subscribers_ids'] = $segment->settings->subscribers_ids;
                    break;

                case 'filter':
                    if(isset($segment->settings->filters_cities)) $_GET['filters_cities'] = $segment->settings->filters_cities ?? [];
                    if(isset($segment->settings->filters_countries)) $_GET['filters_countries'] = $segment->settings->filters_countries ?? [];
                    if(isset($segment->settings->filters_continents)) $_GET['filters_continents'] = $segment->settings->filters_continents ?? [];
                    if(isset($segment->settings->filters_device_type)) $_GET['filters_device_type'] = $segment->settings->filters_device_type ?? [];
                    if(isset($segment->settings->filters_languages)) $_GET['filters_languages'] = $segment->settings->filters_languages ?? [];
                    if(isset($segment->settings->filters_operating_systems)) $_GET['filters_operating_systems'] = $segment->settings->filters_operating_systems ?? [];
                    if(isset($segment->settings->filters_browsers)) $_GET['filters_browsers'] = $segment->settings->filters_browsers ?? [];
                    break;
            }

        }

        switch($type) {
            case 'all':

                $count = db()->where('user_id', $this->user->user_id)->where('website_id', $website_id)->getValue('subscribers', 'COUNT(*)');

                break;

            case 'custom':

                $count = db()->where('user_id', $this->user->user_id)->where('website_id', $_GET['website_id'])->where('subscriber_id', $_GET['subscribers_ids'], 'IN')->getValue('subscribers', 'COUNT(*)');

                break;

            case 'filter':

                $query = db()->where('user_id', $this->user->user_id)->where('website_id', $website_id);

                $has_filters = false;

                /* Cities */
                if(!empty($_GET['filters_cities'])) {
                    $_GET['filters_cities'] = is_array($_GET['filters_cities']) ? $_GET['filters_cities'] : explode(',', $_GET['filters_cities']);

                    if(count($_GET['filters_cities'])) {
                        $_GET['filters_cities'] = array_map(function($city) {
                            return query_clean($city);
                        }, $_GET['filters_cities']);
                        $_GET['filters_cities'] = array_unique($_GET['filters_cities']);

                        $has_filters = true;
                        $query->where('city_name', $_GET['filters_cities'], 'IN');
                    }
                }

                /* Countries */
                if(isset($_GET['filters_countries'])) {
                    $_GET['filters_countries'] = array_filter($_GET['filters_countries'] ?? [], function($country) {
                        return array_key_exists($country, get_countries_array());
                    });

                    $has_filters = true;
                    $query->where('country_code', $_GET['filters_countries'], 'IN');
                }

                /* Continents */
                if(isset($_GET['filters_continents'])) {
                    $_GET['filters_continents'] = array_filter($_GET['filters_continents'] ?? [], function($country) {
                        return array_key_exists($country, get_continents_array());
                    });

                    $has_filters = true;
                    $query->where('continent_code', $_GET['filters_continents'], 'IN');
                }

                /* Device type */
                if(isset($_GET['filters_device_type'])) {
                    $_GET['filters_device_type'] = array_filter($_GET['filters_device_type'] ?? [], function($device_type) {
                        return in_array($device_type, ['desktop', 'tablet', 'mobile']);
                    });

                    $has_filters = true;
                    $query->where('device_type', $_GET['filters_device_type'], 'IN');
                }

                /* Languages */
                if(isset($_GET['filters_languages'])) {
                    $_GET['filters_languages'] = array_filter($_GET['filters_languages'], function($locale) {
                        return array_key_exists($locale, get_locale_languages_array());
                    });

                    $has_filters = true;
                    $query->where('browser_language', $_GET['filters_languages'], 'IN');
                }

                /* Filters operating systems */
                if (isset($_GET['filters_operating_systems'])) {
                    $_GET['filters_operating_systems'] = array_filter($_GET['filters_operating_systems'], function($os_name) {
                        return in_array($os_name, ['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS']);
                    });

                    $has_filters = true;
                    $query->where('os_name', $_GET['filters_operating_systems'], 'IN');
                }

                /* Filters browsers */
                if (isset($_GET['filters_browsers'])) {
                    $_GET['filters_browsers'] = array_filter($_GET['filters_browsers'], function($browser_name) {
                        return in_array($browser_name, ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet']);
                    });

                    $has_filters = true;
                    $query->where('browser_name', $_GET['filters_browsers'], 'IN');
                }

                $count = $has_filters ? $query->getValue('subscribers', 'COUNT(*)') : 0;

                break;

            default:
                $count = null;
                break;
        }

        Response::json('', 'success', ['count' => $count]);
    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('segments');
        }

        if(empty($_POST['selected'])) {
            redirect('segments');
        }

        if(!isset($_POST['type'])) {
            redirect('segments');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.segments')) {
                        Alerts::add_info(l('global.info_message.team_no_access'));
                        redirect('segments');
                    }

                    foreach($_POST['selected'] as $segment_id) {
                        db()->where('segment_id', $segment_id)->where('user_id', $this->user->user_id)->delete('segments');

                        /* Clear the cache */
                        cache()->deleteItem('segments?user_id=' . $this->user->user_id);
                        cache()->deleteItem('segment?segment_id=' . $segment_id);
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('segments');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.segments')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('segments');
        }

        if(empty($_POST)) {
            redirect('segments');
        }

        $segment_id = (int) query_clean($_POST['segment_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$segment = db()->where('segment_id', $segment_id)->where('user_id', $this->user->user_id)->getOne('segments', ['segment_id', 'name'])) {
            redirect('segments');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('segment_id', $segment_id)->delete('segments');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $segment->name . '</strong>'));

            /* Clear the cache */
            cache()->deleteItem('segments?user_id=' . $this->user->user_id);
            cache()->deleteItem('segment?segment_id=' . $segment_id);

            redirect('segments');
        }

        redirect('segments');
    }
}
