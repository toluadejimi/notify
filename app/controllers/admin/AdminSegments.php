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
use Altum\Models\Campaign;

class AdminSegments extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'type', 'segment_id'], ['name',], ['name', 'datetime', 'last_datetime', 'total_subscribers']));
        $filters->set_default_order_by('segment_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `segments` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/segments?' . $filters->get_get() . '&page=%d')));

        /* Get the segments list for the user */
        $segments = [];
        $segments_result = database()->query("
            SELECT
                `segments`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `websites`.`host`, `websites`.`path`
            FROM
                `segments`
            LEFT JOIN
                `users` ON `segments`.`user_id` = `users`.`user_id`
            LEFT JOIN
                `websites` ON `segments`.`website_id` = `websites`.`website_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('segments')}
                {$filters->get_sql_order_by('segments')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $segments_result->fetch_object()) {
            $segments[] = $row;
        }

        /* Export handler */
        process_export_json($segments, 'include', ['segment_id', 'name', 'type', 'total_subscribers', 'settings', 'datetime', 'last_datetime',]);
        process_export_csv($segments, 'include', ['segment_id', 'name', 'type', 'total_subscribers', 'datetime', 'last_datetime',]);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'segments' => $segments,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('admin/segments/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/segments');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/segments');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/segments');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $segment_id) {
                        if($segment = db()->where('segment_id', $segment_id)->getOne('segments', ['user_id', 'segment_id'])) {
                            db()->where('segment_id', $segment_id)->delete('segments');

                            /* Clear the cache */
                            cache()->deleteItem('segments?user_id=' . $segment->user_id);
                            cache()->deleteItem('segment?segment_id=' . $segment_id);
                        }
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/segments');
    }

    public function delete() {

        $segment_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$segment = db()->where('segment_id', $segment_id)->getOne('segments', ['user_id', 'segment_id', 'name'])) {
            redirect('admin/segments');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('segment_id', $segment_id)->delete('segments');

            /* Clear the cache */
            cache()->deleteItem('segments?user_id=' . $segment->user_id);
            cache()->deleteItem('segment?segment_id=' . $segment_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $segment->name . '</strong>'));

        }

        redirect('admin/segments');
    }
}
