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

class Campaigns extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'status', 'segment'], ['name', 'title', 'description', 'url',], ['name', 'title', 'datetime', 'scheduled_datetime', 'last_sent_datetime', 'last_datetime', 'total_push_notifications', 'total_sent_push_notifications', 'total_displayed_push_notifications', 'total_clicked_push_notifications', 'total_closed_push_notifications']));
        $filters->set_default_order_by('campaign_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `campaigns` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('campaigns?' . $filters->get_get() . '&page=%d')));

        /* Get the campaigns list for the user */
        $campaigns = [];
        $campaigns_result = database()->query("SELECT * FROM `campaigns` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $campaigns_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $campaigns[] = $row;
        }

        /* Export handler */
        process_export_json($campaigns, 'include', ['campaign_id', 'user_id', 'name', 'title', 'description', 'url', 'segment', 'status', 'subscribers_ids', 'sent_subscribers_ids', 'total_push_notifications', 'total_sent_push_notifications', 'total_displayed_push_notifications', 'total_clicked_push_notifications', 'total_closed_push_notifications', 'scheduled_datetime', 'last_sent_datetime', 'datetime', 'last_datetime',]);
        process_export_csv($campaigns, 'include', ['campaign_id', 'user_id', 'name', 'title', 'description', 'url', 'segment', 'status', 'subscribers_ids', 'sent_subscribers_ids', 'total_push_notifications', 'total_sent_push_notifications', 'total_displayed_push_notifications', 'total_clicked_push_notifications', 'total_closed_push_notifications', 'scheduled_datetime', 'last_sent_datetime', 'datetime', 'last_datetime',]);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        /* Available */
        $campaigns_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`pusher_campaigns_current_month`');

        /* Prepare the view */
        $data = [
            'campaigns' => $campaigns,
            'websites' => $websites,
            'total_campaigns' => $total_rows,
            'domains' => $domains,
            'pagination' => $pagination,
            'filters' => $filters,
            'campaigns_current_month' => $campaigns_current_month,
        ];

        $view = new \Altum\View('campaigns/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.campaigns')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('campaigns');
        }

        if(empty($_POST)) {
            redirect('campaigns');
        }

        /* Check for the plan limit */
        $campaigns_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`pusher_campaigns_current_month`');
        if($this->user->plan_settings->campaigns_per_month_limit != -1 && $campaigns_current_month >= $this->user->plan_settings->campaigns_per_month_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('campaigns');
        }

        $campaign_id = (int) $_POST['campaign_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('campaigns');
        }

        /* Verify the main resource */
        if(!$campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns')) {
            redirect('campaigns');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Duplicate the files */
            $image = \Altum\Uploads::copy_uploaded_file($campaign->image, \Altum\Uploads::get_path('websites_campaigns_images'), \Altum\Uploads::get_path('websites_campaigns_images'));

            /* Insert to database */
            $campaign_id = db()->insert('campaigns', [
                'website_id' => $campaign->website_id,
                'user_id' => $this->user->user_id,
                'name' => string_truncate($campaign->name . ' - ' . l('global.duplicated'), 64, null),
                'title' => $campaign->title,
                'description' => $campaign->description,
                'url' => $campaign->url,
                'image' => $image,
                'segment' => $campaign->segment,
                'settings' => $campaign->settings,
                'subscribers_ids' => $campaign->subscribers_ids,
                'sent_subscribers_ids' => '[]',
                'total_push_notifications' => $campaign->total_push_notifications,
                'status' => 'draft',
                'scheduled_datetime' => $campaign->scheduled_datetime,
                'datetime' => \Altum\Date::$date,
            ]);

            /* Clear the cache */
            cache()->deleteItem('campaigns?user_id=' . $this->user->user_id);
            cache()->deleteItem('campaigns_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('campaigns_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($campaign->name) . '</strong>'));

            /* Redirect */
            redirect('campaign-update/' . $campaign_id);

        }

        redirect('campaigns');
    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('campaigns');
        }

        if(empty($_POST['selected'])) {
            redirect('campaigns');
        }

        if(!isset($_POST['type'])) {
            redirect('campaigns');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.campaigns')) {
                        Alerts::add_info(l('global.info_message.team_no_access'));
                        redirect('campaigns');
                    }

                    foreach($_POST['selected'] as $campaign_id) {
                        if($campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns', ['campaign_id', 'website_id'])) {
                            (new Campaign())->delete($campaign_id);
                        }
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('campaigns');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.campaigns')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('campaigns');
        }

        if(empty($_POST)) {
            redirect('campaigns');
        }

        $campaign_id = (int) query_clean($_POST['campaign_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns', ['campaign_id', 'name'])) {
            redirect('campaigns');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new Campaign())->delete($campaign_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $campaign->name . '</strong>'));

            redirect('campaigns');
        }

        redirect('campaigns');
    }
}
