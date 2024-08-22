<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Controllers;


class Dashboard extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Get some stats */
        $total_websites = \Altum\Cache::cache_function_result('websites_total?user_id=' . $this->user->user_id, null, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('websites', 'count(*)');
        });

        $total_subscribers = \Altum\Cache::cache_function_result('subscribers_total?user_id=' . $this->user->user_id, null, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('websites', 'sum(total_subscribers)');
        });

        $total_campaigns = \Altum\Cache::cache_function_result('campaigns_total?user_id=' . $this->user->user_id, null, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('campaigns', 'count(*)');
        });

        $total_sent_push_notifications = \Altum\Cache::cache_function_result('total_sent_push_notifications_total?user_id=' . $this->user->user_id, null, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('campaigns', 'SUM(total_sent_push_notifications)');
        });

        /* Get subscribers */
        $subscribers = \Altum\Cache::cache_function_result('subscribers_dashboard?user_id=' . $this->user->user_id, null, function() {
            $subscribers = [];
            $subscribers_result = database()->query("SELECT * FROM `subscribers` WHERE `user_id` = {$this->user->user_id} ORDER BY `subscriber_id` DESC LIMIT 5");
            while ($row = $subscribers_result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');
                $subscribers[] = $row;
            }

            return $subscribers;
        });

        /* Get campaigns */
        $campaigns = \Altum\Cache::cache_function_result('campaigns_dashboard?user_id=' . $this->user->user_id, null, function() {
            $campaigns = [];
            $campaigns_result = database()->query("SELECT * FROM `campaigns` WHERE `user_id` = {$this->user->user_id} ORDER BY `campaign_id` DESC LIMIT 5");
            while ($row = $campaigns_result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');
                $campaigns[] = $row;
            }

            return $campaigns;
        });

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);
        $websites = array_reverse($websites, true);

        /* Get statistics */
        if(count($websites)) {
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
                    AND (`datetime` BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`,
                    `type`
                ORDER BY
                    `formatted_date`
            ";

            $subscribers_logs_chart = \Altum\Cache::cache_function_result('subscribers_logs?user_id=' . $this->user->user_id, null, function() use ($subscribers_logs_result_query) {
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
                            'subscribed' => 0,
                            'unsubscribed' => 0,
                        ], [
                            $row->type => $row->total,
                        ]);
                }

                return $subscribers_logs_chart;
            }, 60 * 60 * settings()->main->chart_cache ?? 12);

            $subscribers_logs_chart = get_chart_data($subscribers_logs_chart);
        }

        /* Get current monthly usage */
        $usage = db()->where('user_id', $this->user->user_id)->getOne('users', ['pusher_sent_push_notifications_current_month', 'pusher_campaigns_current_month',]);

        /* Prepare the view */
        $data = [
            'subscribers_logs_chart' => $subscribers_logs_chart ?? null,
            'websites' => $websites,
            'subscribers' => $subscribers,
            'campaigns' => $campaigns,
            'domains' => $domains,
            'total_websites' => $total_websites,
            'total_subscribers' => $total_subscribers,
            'total_campaigns' => $total_campaigns,
            'total_sent_push_notifications' => $total_sent_push_notifications,
            'usage' => $usage,
        ];

        $view = new \Altum\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
