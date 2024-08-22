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
use Altum\Models\Barcode;
use Altum\Models\Domain;

class Subscribers extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'device_type', 'country_code', 'continent_code'], ['ip', 'city_name', 'os_name', 'browser_name', 'browser_language', 'subscribed_on_url'], ['last_sent_datetime', 'datetime', 'last_datetime', 'total_sent_push_notifications']));
        $filters->set_default_order_by('subscriber_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `subscribers` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('subscribers?' . $filters->get_get() . '&page=%d')));

        /* Get the subscribers list for the user */
        $subscribers = [];
        $subscribers_result = database()->query("SELECT * FROM `subscribers` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $subscribers_result->fetch_object()) $subscribers[] = $row;

        /* Export handler */
        process_export_json($subscribers, 'include', ['subscriber_id', 'website_id', 'unique_endpoint_id', 'endpoint', 'keys', 'ip', 'custom_parameters', 'city_name', 'country_code', 'continent_code', 'os_name', 'browser_name', 'browser_language', 'device_type', 'subscribed_on_url', 'total_sent_push_notifications', 'last_sent_datetime', 'datetime', 'last_datetime']);
        process_export_csv($subscribers, 'include', ['subscriber_id', 'website_id', 'unique_endpoint_id', 'endpoint', 'ip', 'city_name', 'country_code', 'continent_code', 'os_name', 'browser_name', 'browser_language', 'device_type', 'subscribed_on_url', 'total_sent_push_notifications', 'last_sent_datetime', 'datetime', 'last_datetime']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'subscribers' => $subscribers,
            'websites' => $websites,
            'total_subscribers' => $total_rows,
            'domains' => $domains,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('subscribers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('subscribers');
        }

        if(empty($_POST['selected'])) {
            redirect('subscribers');
        }

        if(!isset($_POST['type'])) {
            redirect('subscribers');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.subscribers')) {
                        Alerts::add_info(l('global.info_message.team_no_access'));
                        redirect('subscribers');
                    }

                    foreach($_POST['selected'] as $subscriber_id) {
                        if($subscriber = db()->where('subscriber_id', $subscriber_id)->where('user_id', $this->user->user_id)->getOne('subscribers', ['subscriber_id', 'website_id', 'ip'])) {

                            /* Update all previous logs */
                            db()->where('subscriber_id', $subscriber_id)->update('subscribers_logs', [
                                'ip' => preg_replace('/\d/', '*', $subscriber->ip)
                            ]);

                            /* Database query */
                            db()->where('subscriber_id', $subscriber_id)->delete('subscribers');

                            /* Insert subscriber log */
                            db()->insert('subscribers_logs', [
                                'website_id' => $subscriber->website_id,
                                'user_id' => $this->user->user_id,
                                'ip' => preg_replace('/\d/', '*', $subscriber->ip),
                                'type' => 'manually_deleted',
                                'datetime' => \Altum\Date::$date,
                            ]);

                            /* Clear the cache */
                            cache()->deleteItem('subscribers_total?user_id=' . $this->user->user_id);
                            cache()->deleteItem('subscribers_dashboard?user_id=' . $this->user->user_id);

                        }
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('subscribers');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.subscribers')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('subscribers');
        }

        if(empty($_POST)) {
            redirect('subscribers');
        }

        $subscriber_id = (int) query_clean($_POST['subscriber_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$subscriber = db()->where('subscriber_id', $subscriber_id)->where('user_id', $this->user->user_id)->getOne('subscribers', ['subscriber_id', 'website_id', 'ip'])) {
            redirect('subscribers');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Update all previous logs */
            db()->where('subscriber_id', $subscriber_id)->update('subscribers_logs', [
                'ip' => preg_replace('/\d/', '*', $subscriber->ip)
            ]);

            /* Database query */
            db()->where('subscriber_id', $subscriber_id)->delete('subscribers');

            /* Insert subscriber log */
            db()->insert('subscribers_logs', [
                'website_id' => $subscriber->website_id,
                'user_id' => $this->user->user_id,
                'ip' => preg_replace('/\d/', '*', $subscriber->ip),
                'type' => 'manually_deleted',
                'datetime' => \Altum\Date::$date,
            ]);

            /* Clear the cache */
            cache()->deleteItem('subscribers_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('subscribers_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

            redirect('subscribers');
        }

        redirect('subscribers');
    }
}
