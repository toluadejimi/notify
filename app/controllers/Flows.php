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
use Altum\Models\Flow;
use Altum\Response;

class Flows extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'segment'], ['name', 'title', 'description', 'url',], ['name', 'title', 'last_sent_datetime', 'datetime', 'last_datetime', 'total_push_notifications', 'total_sent_push_notifications', 'total_displayed_push_notifications', 'total_clicked_push_notifications', 'total_closed_push_notifications']));
        $filters->set_default_order_by('flow_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `flows` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('flows?' . $filters->get_get() . '&page=%d')));

        /* Get the flows list for the user */
        $flows = [];
        $flows_result = database()->query("SELECT * FROM `flows` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $flows_result->fetch_object()) {
            $flows[] = $row;
        }

        /* Export handler */
        process_export_json($flows, 'include', ['flow_id', 'website_id', 'user_id', 'name', 'title', 'description', 'url', 'image', 'segment', 'settings', 'status', 'total_sent_push_notifications', 'total_displayed_push_notifications', 'total_clicked_push_notifications', 'total_closed_push_notifications', 'last_sent_datetime', 'datetime', 'last_datetime',]);
        process_export_csv($flows, 'include', ['flow_id', 'website_id', 'user_id', 'name', 'title', 'description', 'url', 'image', 'segment', 'status', 'total_sent_push_notifications', 'total_displayed_push_notifications', 'total_clicked_push_notifications', 'total_closed_push_notifications', 'last_sent_datetime', 'datetime', 'last_datetime',]);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        /* Get statistics */
        if(count($flows)) {
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chat_days ?? 30) . ' day')->format('Y-m-d H:i:s');
            $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d H:i:s');

            $subscribers_logs_result_query = "
                SELECT
                    `type`,
                    COUNT(*) AS `total`,
                    DATE_FORMAT(`datetime`, '%Y-%m-%d') AS `formatted_date`
                FROM
                    `subscribers_logs`
                WHERE   
                    `user_id` = {$this->user->user_id} 
                    AND `flow_id` IS NOT NULL
                    AND `type` = 'push_notification_sent'
                    AND (`datetime` BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`,
                    `type`
                ORDER BY
                    `formatted_date`
            ";

            $subscribers_logs_chart = \Altum\Cache::cache_function_result('flows_subscribers_logs?user_id=' . $this->user->user_id, null, function() use ($subscribers_logs_result_query) {
                $subscribers_logs_chart= [];

                $subscribers_logs_result = database()->query($subscribers_logs_result_query);

                /* Generate the raw chart data and save logs for later usage */
                while($row = $subscribers_logs_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5);

                    $subscribers_logs_chart[$label] = isset($subscribers_logs_chart[$label]) ?
                        array_merge($subscribers_logs_chart[$label], [
                            $row->type => $row->total,
                        ]) :
                        array_merge([
                            'push_notification_sent' => 0,
                        ], [
                            $row->type => $row->total,
                        ]);
                }

                return $subscribers_logs_chart;
            }, 60 * 60 * settings()->main->chart_cache ?? 12);

            $subscribers_logs_chart = get_chart_data($subscribers_logs_chart);
        }

        /* Prepare the view */
        $data = [
            'subscribers_logs_chart' => $subscribers_logs_chart ?? null,
            'flows' => $flows,
            'websites' => $websites,
            'total_flows' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('flows/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.flows')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('flows');
        }

        if(empty($_POST)) {
            redirect('flows');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `flows` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->flows_limit != -1 && $total_rows >= $this->user->plan_settings->flows_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('flows');
        }

        $flow_id = (int) $_POST['flow_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('flows');
        }

        /* Verify the main resource */
        if(!$flow = db()->where('flow_id', $flow_id)->where('user_id', $this->user->user_id)->getOne('flows')) {
            redirect('flows');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Duplicate the files */
            $image = \Altum\Uploads::copy_uploaded_file($flow->image, \Altum\Uploads::get_path('websites_flows_images'), \Altum\Uploads::get_path('websites_flows_images'));

            /* Insert to database */
            $flow_id = db()->insert('flows', [
                'website_id' => $flow->website_id,
                'user_id' => $this->user->user_id,
                'name' => string_truncate($flow->name . ' - ' . l('global.duplicated'), 64, null),
                'title' => $flow->title,
                'description' => $flow->description,
                'url' => $flow->url,
                'image' => $image,
                'segment' => $flow->segment,
                'settings' => $flow->settings,
                'wait_time' => $flow->wait_time,
                'wait_time_type' => $flow->wait_time_type,
                'datetime' => \Altum\Date::$date,
            ]);

            /* Clear the cache */
            cache()->deleteItem('flows?user_id=' . $this->user->user_id);
            cache()->deleteItem('flows?website_id=' . $flow->website_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($flow->name) . '</strong>'));

            /* Redirect */
            redirect('flow-update/' . $flow_id);

        }

        redirect('flows');
    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('flows');
        }

        if(empty($_POST['selected'])) {
            redirect('flows');
        }

        if(!isset($_POST['type'])) {
            redirect('flows');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.flows')) {
                        Alerts::add_info(l('global.info_message.team_no_access'));
                        redirect('flows');
                    }

                    foreach($_POST['selected'] as $flow_id) {
                        if($flow = db()->where('flow_id', $flow_id)->where('user_id', $this->user->user_id)->getOne('campaigns', ['flow_id'])) {
                            (new Flow())->delete($flow_id);
                        }
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('flows');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.flows')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('flows');
        }

        if(empty($_POST)) {
            redirect('flows');
        }

        $flow_id = (int) query_clean($_POST['flow_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$flow = db()->where('flow_id', $flow_id)->where('user_id', $this->user->user_id)->getOne('flows', ['flow_id', 'name'])) {
            redirect('flows');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new Flow())->delete($flow_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $flow->name . '</strong>'));

            redirect('flows');
        }

        redirect('flows');
    }
}
