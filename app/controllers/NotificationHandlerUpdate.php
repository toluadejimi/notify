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

class NotificationHandlerUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.notification_handlers')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('notification-handlers');
        }

        $notification_handler_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$notification_handler = db()->where('notification_handler_id', $notification_handler_id)->where('user_id', $this->user->user_id)->getOne('notification_handlers')) {
            redirect('notification-handlers');
        }
        $notification_handler->settings = json_decode($notification_handler->settings ?? '');

        if(!empty($_POST)) {
            $_POST['type'] = array_key_exists($_POST['type'], require APP_PATH . 'includes/notification_handlers.php') ? input_clean($_POST['type']) : null;
            $_POST['name'] = input_clean($_POST['name']);
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['type', 'name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = [];
                switch($_POST['type']) {
                    case 'telegram':
                        $settings['telegram'] = input_clean($_POST['telegram'], 512);
                        $settings['telegram_chat_id'] = input_clean($_POST['telegram_chat_id'], 512);
                        break;

                    case 'whatsapp':
                        $settings['whatsapp'] = (int) input_clean($_POST['whatsapp'], 32);
                        break;

                    case 'twilio':
                    case 'twilio_call':
                        $settings[$_POST['type']] = input_clean($_POST[$_POST['type']], 32);
                        break;

                    default:
                        $settings[$_POST['type']] = input_clean($_POST[$_POST['type']], 512);
                        break;
                }
                $settings = json_encode($settings);

                /* Database query */
                db()->where('notification_handler_id', $notification_handler_id)->update('notification_handlers', [
                    'type' => $_POST['type'],
                    'name' => $_POST['name'],
                    'settings' => $settings,
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('notification_handlers?user_id=' . $this->user->user_id);

                redirect('notification-handler-update/' . $notification_handler_id);
            }
        }

        /* Prepare the view */
        $data = [
            'notification_handler' => $notification_handler,
        ];

        $view = new \Altum\View('notification-handler-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
