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

class WebsiteUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.websites')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('websites');
        }

        $website_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites')) {
            redirect('websites');
        }

        $website->settings = json_decode($website->settings ?? '');
        $website->notifications = json_decode($website->notifications ?? '');
        $website->keys = json_decode($website->keys ?? '');

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['domain_id'] = isset($_POST['domain_id']) && array_key_exists($_POST['domain_id'], $domains) ? (int) $_POST['domain_id'] : null;
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);
            $_POST['ip_storage_is_enabled'] = (int) isset($_POST['ip_storage_is_enabled']);
            $_POST['scheme'] = in_array($_POST['scheme'], ['https://', 'http://']) ? query_clean($_POST['scheme']) : 'https://';

            /* Advanced */
            $_POST['public_key'] = input_clean($_POST['public_key'], 256);
            $_POST['private_key'] = input_clean($_POST['private_key'], 256);

            /* Branding */
            $_POST['branding_name'] = input_clean($_POST['branding_name'], 128);
            $_POST['branding_url'] = get_url($_POST['branding_url']);

            /* Notification handlers */
            $_POST['notifications'] = array_map(
                function($notification_handler_id) {
                    return (int) $notification_handler_id;
                },
                array_filter($_POST['notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'host'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Image uploads */
            $icon = \Altum\Uploads::process_upload($website->settings->icon, 'websites_icons', 'icon', 'icon_remove', settings()->websites->icon_size_limit);

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Domain checking */
                $host = $_POST['host'];
                $path = null;

                if(function_exists('idn_to_utf8')) {
                    $path = parse_url($_POST['scheme'] . idn_to_utf8($host), PHP_URL_PATH);
                    $host = parse_url($_POST['scheme'] . idn_to_utf8($host), PHP_URL_HOST);
                }

                if(function_exists('idn_to_ascii')) {
                    $host = idn_to_ascii($host);
                }

                /* Settings */
                $settings = json_encode([
                    'icon' => $icon,
                    'ip_storage_is_enabled' => $_POST['ip_storage_is_enabled'],
                    'branding_name' => $_POST['branding_name'],
                    'branding_url' => $_POST['branding_url'],
                ]);

                /* Keys */
                $keys = json_encode([
                    'public_key' => $_POST['public_key'],
                    'private_key' => $_POST['private_key'],
                ]);

                /* Notification handlers */
                $notifications = json_encode($_POST['notifications']);

                /* Database query */
                db()->where('website_id', $website->website_id)->update('websites', [
                    'domain_id' => $_POST['domain_id'],
                    'name' => $_POST['name'],
                    'scheme' => $_POST['scheme'],
                    'host' => $host,
                    'path' => $path,
                    'settings' => $settings,
                    'keys' => $keys,
                    'notifications' => $notifications,
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('websites?user_id=' . $this->user->user_id);
                cache()->deleteItem('website?website_id=' . $website->website_id);
                cache()->deleteItem('website?pixel_key=' . $website->pixel_key);

                redirect('website-update/' . $website_id);
            }
        }

        /* Prepare the view */
        $data = [
            'website' => $website,
            'domains' => $domains,
            'notification_handlers' => $notification_handlers,
        ];

        $view = new \Altum\View('website-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
