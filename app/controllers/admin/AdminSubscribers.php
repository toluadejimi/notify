<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\controllers\Controller;
use Altum\Models\Barcode;
use Altum\Models\Domain;
use Altum\Models\Website;

class AdminSubscribers extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'device_type', 'country_code', 'continent_code'], ['ip', 'city_name', 'os_name', 'browser_name', 'browser_language', 'subscribed_on_url'], ['last_sent_datetime', 'datetime', 'last_datetime', 'total_sent_push_notifications']));
        $filters->set_default_order_by('subscriber_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `subscribers` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/subscribers?' . $filters->get_get() . '&page=%d')));

        /* Get the subscribers list for the user */
        $subscribers = [];
        $subscribers_result = database()->query("
            SELECT
                `subscribers`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `websites`.`host`, `websites`.`path`
            FROM
                `subscribers`
            LEFT JOIN
                `users` ON `subscribers`.`user_id` = `users`.`user_id`
            LEFT JOIN
                `websites` ON `subscribers`.`website_id` = `websites`.`website_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('subscribers')}
                {$filters->get_sql_order_by('subscribers')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $subscribers_result->fetch_object()) $subscribers[] = $row;

        /* Export handler */
        process_export_json($subscribers, 'include', ['subscriber_id', 'website_id', 'unique_endpoint_id', 'endpoint', 'keys', 'ip', 'custom_parameters', 'city_name', 'country_code', 'continent_code', 'os_name', 'browser_name', 'browser_language', 'device_type', 'subscribed_on_url', 'total_sent_push_notifications', 'last_sent_datetime', 'datetime', 'last_datetime']);
        process_export_csv($subscribers, 'include', ['subscriber_id', 'website_id', 'unique_endpoint_id', 'endpoint', 'ip', 'city_name', 'country_code', 'continent_code', 'os_name', 'browser_name', 'browser_language', 'device_type', 'subscribed_on_url', 'total_sent_push_notifications', 'last_sent_datetime', 'datetime', 'last_datetime']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'subscribers' => $subscribers,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('admin/subscribers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/subscribers');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/subscribers');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/subscribers');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $subscriber_id) {
                        if($subscriber = db()->where('subscriber_id', $subscriber_id)->getOne('subscribers', ['subscriber_id', 'website_id', 'user_id', 'ip'])) {

                            /* Update all previous logs */
                            db()->where('subscriber_id', $subscriber_id)->update('subscribers_logs', [
                                'ip' => preg_replace('/\d/', '*', $subscriber->ip)
                            ]);

                            /* Database query */
                            db()->where('subscriber_id', $subscriber_id)->delete('subscribers');

                            /* Insert subscriber log */
                            db()->insert('subscribers_logs', [
                                'website_id' => $subscriber->website_id,
                                'user_id' => $subscriber->user_id,
                                'ip' => preg_replace('/\d/', '*', $subscriber->ip),
                                'type' => 'manually_deleted',
                                'datetime' => \Altum\Date::$date,
                            ]);

                        }
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/subscribers');
    }

    public function delete() {

        $subscriber_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$subscriber = db()->where('subscriber_id', $subscriber_id)->getOne('subscribers', ['subscriber_id', 'user_id', 'website_id', 'ip'])) {
            redirect('admin/subscribers');
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
                'user_id' => $subscriber->user_id,
                'ip' => preg_replace('/\d/', '*', $subscriber->ip),
                'type' => 'manually_deleted',
                'datetime' => \Altum\Date::$date,
            ]);

            /* Clear the cache */
            cache()->deleteItem('subscribers_total?user_id=' . $subscriber->user_id);
            cache()->deleteItem('subscribers_dashboard?user_id=' . $subscriber->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

        }

        redirect('admin/subscribers');
    }
}
