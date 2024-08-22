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
use Altum\Title;

class SubscribersLogs extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'campaign_id', 'subscriber_id', 'flow_id', 'type'], ['ip'], ['datetime']));
        $filters->set_default_order_by('subscriber_log_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `subscribers_logs` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('subscribers_logs?' . $filters->get_get() . '&page=%d')));

        /* Get the subscribers_logs list for the user */
        $subscribers_logs = [];
        $subscribers_logs_result = database()->query("SELECT * FROM `subscribers_logs` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $subscribers_logs_result->fetch_object()) $subscribers_logs[] = $row;

        /* Export handler */
        process_export_csv($subscribers_logs, 'include', ['subscriber_log_id', 'subscriber_id', 'campaign_id', 'website_id', 'user_id', 'ip', 'type', 'datetime']);
        process_export_json($subscribers_logs, 'include', ['subscriber_log_id', 'subscriber_id', 'campaign_id', 'website_id', 'user_id', 'ip', 'type', 'datetime']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'subscribers_logs' => $subscribers_logs,
            'pagination' => $pagination,
            'filters' => $filters,
            'websites' => $websites,
        ];

        $view = new \Altum\View('subscribers-logs/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('subscribers-logs');
        }

        if(empty($_POST['selected'])) {
            redirect('subscribers-logs');
        }

        if(!isset($_POST['type'])) {
            redirect('subscribers-logs');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.subscribers_logs')) {
                        Alerts::add_info(l('global.info_message.team_no_access'));
                        redirect('subscribers-logs');
                    }

                    foreach($_POST['selected'] as $subscriber_log_id) {
                        /* Database query */
                        db()->where('subscriber_log_id', $subscriber_log_id)->where('user_id', $this->user->user_id)->delete('subscribers_logs');
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('subscribers-logs');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.subscribers_logs')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('subscribers-logs');
        }

        if(empty($_POST)) {
            redirect('subscribers-logs');
        }

        $subscriber_log_id = (int) query_clean($_POST['subscriber_log_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$subscriber_log = db()->where('subscriber_log_id', $subscriber_log_id)->where('user_id', $this->user->user_id)->getOne('subscribers_logs', ['subscriber_log_id'])) {
            redirect('subscribers-logs');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('subscriber_log_id', $subscriber_log_id)->delete('subscribers_logs');

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

            redirect('subscribers-logs');
        }

        redirect('subscribers-logs');
    }

}
