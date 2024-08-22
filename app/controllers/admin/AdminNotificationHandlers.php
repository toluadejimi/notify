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

class AdminNotificationHandlers extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'type'], ['name'], ['last_datetime', 'datetime', 'name']));
        $filters->set_default_order_by('notification_handler_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `notification_handlers` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/notification-handlers?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $notification_handlers = [];
        $notification_handlers_result = database()->query("
            SELECT
                `notification_handlers`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`
            FROM
                `notification_handlers`
            LEFT JOIN
                `users` ON `notification_handlers`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('notification_handlers')}
                {$filters->get_sql_order_by('notification_handlers')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $notification_handlers_result->fetch_object()) {
            $notification_handlers[] = $row;
        }

        /* Export handler */
        process_export_csv($notification_handlers, 'include', ['notification_handler_id', 'user_id', 'name', 'type', 'last_datetime', 'datetime'], sprintf(l('admin_notification_handlers.title')));
        process_export_json($notification_handlers, 'include', ['notification_handler_id', 'user_id', 'name', 'type', 'settings', 'last_datetime', 'datetime'], sprintf(l('admin_notification_handlers.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'notification_handlers' => $notification_handlers,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/notification-handlers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/notification-handlers');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/notification-handlers');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/notification-handlers');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $notification_handler_id) {
                        if($notification_handler = db()->where('notification_handler_id', $notification_handler_id)->where('user_id', $this->user->user_id)->getOne('notification_handlers', ['domain_id', 'user_id'])) {
                            /* Delete the notification handler */
                            db()->where('notification_handler_id', $notification_handler_id)->delete('notification_handlers');

                            /* Clear the cache */
                            cache()->deleteItem('notification_handlers?user_id=' . $notification_handler->user_id);
                        }
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/notification-handlers');
    }

    public function delete() {

        $notification_handler_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$notification_handler = db()->where('notification_handler_id', $notification_handler_id)->getOne('notification_handlers', ['notification_handler_id', 'name', 'user_id'])) {
            redirect('admin/notification-handlers');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the notification handler */
            db()->where('notification_handler_id', $notification_handler->notification_handler_id)->delete('notification_handlers');

            /* Clear the cache */
            cache()->deleteItem('notification_handlers?user_id=' . $notification_handler->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $notification_handler->name . '</strong>'));

        }

        redirect('admin/notification-handlers');
    }

}
