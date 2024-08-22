<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Controllers;

use Altum\Models\Campaign;
use Altum\Models\Subscriber;
use Altum\Models\User;
use MaxMind\Db\Reader;

class PixelTrack extends Controller {

    public function index() {

        /* Get the Payload of the Post */
        $payload = @file_get_contents('php://input');
        $_POST = json_decode($payload, true);

        /* Check for any errors */
        $required_fields = ['type'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                redirect();
            }
        }

        $allowed_types = [
            /* Subscriber */
            'create',
            'delete',

            /* Notifications */
            'displayed_notification',
            'clicked_notification',
            'closed_notification',

            /* Permissions */
            'permission_denied',
        ];

        if(!in_array($_POST['type'], $allowed_types)) {
            die(settings()->main->title . ' (' . SITE_URL . '): Provided type not allowed.');
        }

        $pixel_key = isset($this->params[0]) ? input_clean($this->params[0]) : null;

        /* Get the details of the website from the database */
        $website = (new \Altum\Models\Website())->get_website_by_pixel_key($pixel_key);

        /* Make sure the website has access */
        if(!$website) {
            die(settings()->main->title . ' (' . SITE_URL . '): No website found for this pixel.');
        }

        if(!$website->is_enabled) {
            die(settings()->main->title . ' (' . SITE_URL . '): Website disabled.');
        }

        /* Make sure to get the user data and confirm the user is ok */
        $user = (new \Altum\Models\User())->get_user_by_user_id($website->user_id);

        if(!$user) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Website owner not found.')");
        }

        if($user->status != 1) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Website owner is disabled.')");
        }

        /* Check for a custom domain */
        if(isset(\Altum\Router::$data['domain']) && $website->domain_id != \Altum\Router::$data['domain']->domain_id) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Domain id mismatch.')");
        }

        /* Process the plan of the user */
        (new User())->process_user_plan_expiration_by_user($user);

        /* Create and Delete handlers */
        if(in_array($_POST['type'], ['create', 'delete'])) {
            /* Check for any errors */
            $required_fields = ['url', 'endpoint', 'p256dh', 'auth'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    redirect();
                }
            }

            /* Parse the data */
            $_POST['endpoint'] = trim(filter_var($_POST['endpoint'], FILTER_SANITIZE_URL));
            $_POST['url'] = parse_url($_POST['url'], PHP_URL_HOST) == $website->host ? input_clean($_POST['url'], 2048) : null;
            $unique_endpoint_id = md5($_POST['endpoint']);
            $keys = json_encode([
                'p256dh' => $_POST['p256dh'],
                'auth' => $_POST['auth'],
            ]);

            /* Make sure only whitelisted endpoints are accepted */
            $endpoint = parse_url($_POST['endpoint']);
            $whitelisted_hosts = [
                'android.googleapis.com',
                'fcm.googleapis.com',
                'updates.push.services.mozilla.com',
                'updates-autopush.stage.mozaws.net',
                'updates-autopush.dev.mozaws.net',
                'notify.windows.com',
                'push.apple.com',
            ];

            $accepted = false;
            foreach($whitelisted_hosts as $whitelisted_host) {
                if(string_ends_with($whitelisted_host, $endpoint['host'])) {
                    $accepted = true;
                }
            }

            if(!$accepted) {
                die("console.log('" . settings()->main->title . " (" . SITE_URL . "): Endpoint not allowed.')");
            }
        }

        $ip = get_ip();
        $original_ip = $ip;

        /* Check if we can save the real IP or not */
        $ip = $website->settings->ip_storage_is_enabled ? $ip : preg_replace('/\d/', '*', $ip);

        switch($_POST['type']) {
            case 'create':

                /* Check for the plan limit */
                $websites = (new \Altum\Models\Website())->get_websites_by_user_id($user->user_id);
                $total_subscribers = 0;
                foreach($websites as $row) { $total_subscribers += $row->total_subscribers; }
                if($user->plan_settings->subscribers_limit != -1 && $total_subscribers >= $user->plan_settings->subscribers_limit) {
                    die("console.log('" . settings()->main->title . " (" . SITE_URL . "): Subscribers limit reached.')");
                }

                /* Detect the location */
                try {
                    $maxmind = (new Reader(APP_PATH . 'includes/GeoLite2-City.mmdb'))->get($original_ip);
                } catch(\Exception $exception) {
                    /* :) */
                }
                $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
                $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
                $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

                /* Detect extra details about the user */
                $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
                $browser_name = $whichbrowser->browser->name ?? null;
                $os_name = $whichbrowser->os->name ?? null;
                $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
                $device_type = get_device_type($_SERVER['HTTP_USER_AGENT']);

                /* Check for custom parameters */
                $custom_parameters = [];

                if(isset($_POST['custom_parameters'])) {
                    $i = 1;
                    foreach((array) $_POST['custom_parameters'] as $key => $value) {
                        $key = input_clean($key, '64');
                        $value = input_clean($value, '512');

                        if($i++ >= 10) {
                            break;
                        } else {
                            $custom_parameters[$key] = $value;
                        }
                    }
                }

                $custom_parameters = json_encode($custom_parameters);

                /* Insert / update */
                $subscriber_id = db()->onDuplicate([
                    'endpoint', 'keys',
                ])->insert('subscribers', [
                    'website_id' => $website->website_id,
                    'user_id' => $website->user_id,
                    'unique_endpoint_id' => $unique_endpoint_id,
                    'endpoint' => $_POST['endpoint'],
                    'keys' => $keys,
                    'ip' => $ip,
                    'custom_parameters' => $custom_parameters,
                    'city_name' => $city_name,
                    'country_code' => $country_code,
                    'continent_code' => $continent_code,
                    'os_name' => $os_name,
                    'browser_name' => $browser_name,
                    'browser_language' => $browser_language,
                    'device_type' => $device_type,
                    'subscribed_on_url' => $_POST['url'],
                    'datetime' => \Altum\Date::$date,
                ]);

                /* Update website statistics */
                if(db()->count == 1) {
                    db()->where('website_id', $website->website_id)->update('websites', ['total_subscribers' => db()->inc()]);

                    /* Clear the cache */
                    cache()->deleteItem('subscribers_total?user_id=' . $website->user_id);
                    cache()->deleteItem('subscribers_dashboard?user_id=' . $website->user_id);

                    /* Processing the notification handlers */
                    if(count($website->notifications ?? [])) {
                        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($user->user_id);

                        /* Processing the notification handlers */
                        foreach($notification_handlers as $notification_handler) {
                            if(!$notification_handler->is_enabled) continue;
                            if(!in_array($notification_handler->notification_handler_id, $website->notifications)) continue;

                            $subscriber_data = [
                                'website_id' => $website->website_id,
                                'url' => url('subscriber/' . $subscriber_id),
                                'ip' => $ip,
                                'subscribed_on_url' => $_POST['url'],
                                'city_name' => $city_name,
                                'country_code' => $country_code,
                                'continent_code' => $continent_code,
                                'os_name' => $os_name,
                                'browser_name' => $browser_name,
                                'browser_language' => $browser_language,
                                'device_type' => $device_type,
                            ];

                            switch($notification_handler->type) {
                                case 'email':

                                    /* Prepare the html for the email body */
                                    $email_body = '<ul>';
                                    foreach($subscriber_data as $key => $value) {
                                        $email_body .= '<li><strong>' . $key . ':</strong>' . ' ' . $value;
                                    }
                                    $email_body .= '</ul>';

                                    $email_template = get_email_template(
                                        [
                                            '{{WEBSITE_NAME}}' => $website->name,
                                        ],
                                        l('global.emails.user_new_subscriber.subject', $user->language),
                                        [
                                            '{{SUBSCRIBER_IP}}' => $ip,
                                            '{{WEBSITE_NAME}}' => $website->name,
                                            '{{WEBSITE_URL}}' => $website->scheme . $website->host . $website->path,
                                            '{{DATA}}' => $email_body
                                        ],
                                        l('global.emails.user_new_subscriber.body', $user->language),
                                    );

                                    /* Send the email */
                                    send_mail($notification_handler->settings->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

                                    break;

                                case 'webhook':

                                    try {
                                        \Unirest\Request::post($notification_handler->settings->webhook, [], $subscriber_data);
                                    } catch (\Exception $exception) {
                                        error_log($exception->getMessage());
                                    }

                                    break;

                                case 'slack':

                                    $caught_data = "\r\n\r\n";
                                    foreach($subscriber_data as $key => $value) {
                                        $caught_data .= '*' . $key . '*: ' .$value . "\r\n";
                                    }
                                    $caught_data .= "\r\n\r\n";

                                    try {
                                        \Unirest\Request::post(
                                            $notification_handler->settings->slack,
                                            ['Accept' => 'application/json'],
                                            \Unirest\Request\Body::json([
                                                'text' => sprintf(
                                                    l('websites.simple_notification', $user->language),
                                                    $website->name,
                                                    $website->scheme . $website->host . $website->path,
                                                    $caught_data,
                                                    url('subscriber/' . $subscriber_id)
                                                ),
                                                'username' => settings()->main->title,
                                                'icon_emoji' => ':large_green_circle:'
                                            ])
                                        );
                                    } catch (\Exception $exception) {
                                        error_log($exception->getMessage());
                                    }

                                    break;

                                case 'discord':

                                    $caught_data = "\r\n\r\n";
                                    foreach($subscriber_data as $key => $value) {
                                        $caught_data .= '*' . $key . '*: ' .$value . "\r\n";
                                    }
                                    $caught_data .= "\r\n\r\n";

                                    try {
                                        \Unirest\Request::post(
                                            $notification_handler->settings->discord,
                                            [
                                                'Accept' => 'application/json',
                                                'Content-Type' => 'application/json',
                                            ],
                                            \Unirest\Request\Body::json([
                                                'embeds' => [
                                                    [
                                                        'title' => sprintf(
                                                            l('websites.simple_notification', $user->language),
                                                            $website->name,
                                                            $website->name,
                                                            $caught_data,
                                                            url('subscriber/' . $subscriber_id)
                                                        ),
                                                        'color' => '2664261',
                                                    ]
                                                ],
                                            ])
                                        );
                                    } catch (\Exception $exception) {
                                        error_log($exception->getMessage());
                                    }

                                    break;

                                case 'telegram':

                                    $caught_data = urlencode("\r\n\r\n");
                                    foreach($subscriber_data as $key => $value) {
                                        $caught_data .= '<strong>' . $key . '</strong>: ' . $value . urlencode("\r\n");
                                    }
                                    $caught_data .= urlencode("\r\n\r\n");

                                    try {
                                        $test = \Unirest\Request::get(
                                            sprintf(
                                                'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s&parse_mode=html',
                                                $notification_handler->settings->telegram,
                                                $notification_handler->settings->telegram_chat_id,
                                                sprintf(
                                                    l('websites.simple_notification', $user->language),
                                                    $website->name,
                                                    $website->scheme . $website->host . $website->path,
                                                    $caught_data,
                                                    url('subscriber/' . $subscriber_id)
                                                )
                                            )
                                        );
                                    } catch (\Exception $exception) {
                                        error_log($exception->getMessage());
                                    }

                                    break;

                                case 'microsoft_teams':

                                    $caught_data = "\r\n\r\n";
                                    foreach($subscriber_data as $key => $value) {
                                        $caught_data .= $key . ': ' .$value . "\r\n";
                                    }
                                    $caught_data .= "\r\n\r\n";

                                    try {
                                        \Unirest\Request::post(
                                            $notification_handler->settings->microsoft_teams,
                                            ['Content-Type' => 'application/json'],
                                            \Unirest\Request\Body::json([
                                                'text' => sprintf(
                                                    l('websites.simple_notification', $user->language),
                                                    $website->name,
                                                    $website->scheme . $website->host . $website->path,
                                                    $caught_data,
                                                    url('subscriber/' . $subscriber_id)
                                                ),
                                            ])
                                        );
                                    } catch (\Exception $exception) {
                                        error_log($exception->getMessage());
                                    }

                                    break;

                                case 'twilio':

                                    try {
                                        \Unirest\Request::auth(settings()->notification_handlers->twilio_sid, settings()->notification_handlers->twilio_token);

                                        \Unirest\Request::post(
                                            sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', settings()->notification_handlers->twilio_sid),
                                            [],
                                            [
                                                'From' => settings()->notification_handlers->twilio_number,
                                                'To' => $notification_handler->settings->twilio,
                                                'Body' => sprintf(
                                                    l('websites.simple_notification', $user->language),
                                                    $website->name,
                                                    $website->scheme . $website->host . $website->path,
                                                    "\r\n\r\n",
                                                    url('subscriber/' . $subscriber_id)
                                                ),
                                            ]
                                        );
                                    } catch (\Exception $exception) {
                                        error_log($exception->getMessage());
                                    }

                                    \Unirest\Request::auth('', '');

                                    break;

                                case 'twilio_call':

                                    try {
                                        \Unirest\Request::auth(settings()->notification_handlers->twilio_sid, settings()->notification_handlers->twilio_token);

                                        \Unirest\Request::post(
                                            sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Calls.json', settings()->notification_handlers->twilio_sid),
                                            [],
                                            [
                                                'From' => settings()->notification_handlers->twilio_number,
                                                'To' => $notification_handler->settings->twilio_call,
                                                'Url' => SITE_URL . 'twiml/notification.simple_notification?param1=' . urlencode($website->name) . '&param2=' . urlencode($website->scheme . $website->host . $website->path) . '&param3=&param4=' . urlencode(url('subscriber/' . $subscriber_id)),
                                            ]
                                        );
                                    } catch (\Exception $exception) {
                                        error_log($exception->getMessage());
                                    }

                                    \Unirest\Request::auth('', '');

                                    break;

                                case 'whatsapp':

                                    try {
                                        $test = \Unirest\Request::post(
                                            'https://graph.facebook.com/v18.0/' . settings()->notification_handlers->whatsapp_number_id . '/messages',
                                            [
                                                'Authorization' => 'Bearer ' . settings()->notification_handlers->whatsapp_access_token,
                                                'Content-Type' => 'application/json'
                                            ],
                                            \Unirest\Request\Body::json([
                                                'messaging_product' => 'whatsapp',
                                                'to' => $notification_handler->settings->whatsapp,
                                                'type' => 'template',
                                                'template' => [
                                                    'name' => 'new_subscriber',
                                                    'language' => [
                                                        'code' => \Altum\Language::$default_code
                                                    ],
                                                    'components' => [[
                                                        'type' => 'body',
                                                        'parameters' => [
                                                            [
                                                                'type' => 'text',
                                                                'text' => $website->name
                                                            ],
                                                            [
                                                                'type' => 'text',
                                                                'text' => $website->scheme . $website->host . $website->path
                                                            ],
                                                            [
                                                                'type' => 'text',
                                                                'text' => url('subscriber/' . $subscriber_id)
                                                            ],
                                                        ]
                                                    ]]

                                                ]
                                            ])
                                        );
                                    } catch (\Exception $exception) {
                                        error_log($exception->getMessage());
                                    }


                                    break;

                                case 'push_subscriber_id':
                                    $push_subscriber = db()->where('push_subscriber_id', $notification_handler->settings->push_subscriber_id)->getOne('push_subscribers');
                                    if(!$push_subscriber) {
                                        db()->where('notification_handler_id', $notification_handler->notification_handler_id)->update('notification_handlers', ['is_enabled' => 0]);
                                    };

                                    /* Prepare the web push */
                                    $push_notification = \Altum\Helpers\PushNotifications::send([
                                        'title' => l('websites.push_notification.title', $user->language),
                                        'description' => sprintf(l('websites.push_notification.description', $user->language), $website->name, $website->scheme . $website->host . $website->path),
                                        'url' => url('subscriber/' . $subscriber_id),
                                    ], $push_subscriber);

                                    /* Unsubscribe if push failed */
                                    if(!$push_notification) {
                                        db()->where('push_subscriber_id', $push_subscriber->push_subscriber_id)->delete('push_subscribers');
                                        db()->where('notification_handler_id', $notification_handler->notification_handler_id)->update('notification_handlers', ['is_enabled' => 0]);
                                    }

                                    break;
                            }
                        }
                    }
                }

                /* Update/resub on an already subscribed user */
                else {
                    /* Insert subscriber log */
                    db()->insert('subscribers_logs', [
                        'website_id' => $website->website_id,
                        'user_id' => $website->user_id,
                        'type' => 'unsubscribed',
                        'ip' => $ip,
                        'datetime' => \Altum\Date::$date,
                    ]);
                }

                /* Insert subscriber log */
                db()->insert('subscribers_logs', [
                    'subscriber_id' => $subscriber_id,
                    'website_id' => $website->website_id,
                    'user_id' => $website->user_id,
                    'ip' => $ip,
                    'type' => 'subscribed',
                    'datetime' => \Altum\Date::$date,
                ]);

                /* Check for potential flows */
                $flows = (new \Altum\Models\Flow())->get_flows_by_website_id($website->website_id);

                /* Go through each flow and set up the scheduled notifications */
                foreach($flows as $flow) {
                    if(!$flow->is_enabled) continue;

                    /* Make sure the subscriber triggers the selected segment */
                    $flow_is_triggered = false;

                    /* Segment */
                    if(is_numeric($flow->segment)) {
                        /* Get settings from custom segments */
                        $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($flow->segment);

                        if(!$segment) {
                            $flow->segment = 'all';
                        }
                    }

                    switch($flow->segment) {
                        case 'all':
                            $flow_is_triggered = true;
                            break;

                        default:
                            /* Assume the flow is triggered */
                            $flow_is_triggered = true;

                            if(count($segment->settings->filters_countries) && !in_array($country_code, $segment->settings->filters_countries)) {
                                $flow_is_triggered = false;
                            }

                            if(count($segment->settings->filters_continents) && !in_array($continent_code, $segment->settings->filters_continents)) {
                                $flow_is_triggered = false;
                            }

                            if(count($segment->settings->filters_device_type) && !in_array($device_type, $segment->settings->filters_device_type)) {
                                $flow_is_triggered = false;
                            }

                            if(count($segment->settings->filters_device_type) && !in_array($device_type, $segment->settings->filters_device_type)) {
                                $flow_is_triggered = false;
                            }

                            if(count($segment->settings->filters_languages) && !in_array($browser_language, $segment->settings->filters_languages)) {
                                $flow_is_triggered = false;
                            }

                            if(count($segment->settings->filters_operating_systems) && !in_array($os_name, $segment->settings->filters_operating_systems)) {
                                $flow_is_triggered = false;
                            }

                            if(count($segment->settings->filters_browsers) && !in_array($browser_name, $segment->settings->filters_browsers)) {
                                $flow_is_triggered = false;
                            }
                            break;
                    }

                    /* Ignore if it's not triggered */
                    if(!$flow_is_triggered) continue;

                    /* Scheduled date */
                    $scheduled_datetime = (new \DateTime())->modify('+' . $flow->wait_time . ' ' . $flow->wait_time_type)->format('Y-m-d H:i:s');

                    /* Insert the scheduled the notification */
                    db()->insert('flow_notifications', [
                        'subscriber_id' => $subscriber_id,
                        'website_id' => $website->website_id,
                        'user_id' => $website->user_id,
                        'flow_id' => $flow->flow_id,
                        'datetime' => \Altum\Date::$date,
                        'scheduled_datetime' => $scheduled_datetime,
                    ]);

                }

                break;

            case 'delete':

                /* Delete subscriber */
                db()->where('unique_endpoint_id', $unique_endpoint_id)->delete('subscribers');

                /* Update website statistics */
                if(db()->count) {
                    db()->where('website_id', $website->website_id)->update('websites', ['total_subscribers' => db()->dec()]);

                    /* Clear the cache */
                    cache()->deleteItem('subscribers_total?user_id=' . $website->user_id);
                    cache()->deleteItem('subscribers_dashboard?user_id=' . $website->user_id);
                }

                /* Insert subscriber log */
                db()->insert('subscribers_logs', [
                    'website_id' => $website->website_id,
                    'user_id' => $website->user_id,
                    'type' => 'unsubscribed',
                    'ip' => $ip,
                    'datetime' => \Altum\Date::$date,
                ]);

                break;

            case 'displayed_notification':
            case 'clicked_notification':
            case 'closed_notification':

                /* Only track those stats if the user has the right plan settings */
                if(!$user->plan_settings->analytics_is_enabled) {
                    break;
                }

                /* Check for any errors */
                $required_fields = ['subscriber_id',];
                foreach($required_fields as $field) {
                    if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                        redirect();
                    }
                }

                $subscriber_id = (int) $_POST['subscriber_id'];

                /* More stats recording */
                $stat_table_column = match ($_POST['type']) {
                    'displayed_notification' => 'total_displayed_push_notifications',
                    'clicked_notification' => 'total_clicked_push_notifications',
                    'closed_notification' => 'total_closed_push_notifications',
                };

                /* Get the subscriber */
                $subscriber = (new Subscriber())->get_subscriber_by_subscriber_id($subscriber_id);

                /* Campaign tracking log */
                if(isset($_POST['campaign_id'])) {
                    $campaign_id = (int) $_POST['campaign_id'];

                    /* Insert subscriber log */
                    db()->insert('subscribers_logs', [
                        'subscriber_id' => $subscriber->subscriber_id,
                        'website_id' => $website->website_id,
                        'user_id' => $website->user_id,
                        'campaign_id' => $campaign_id,
                        'type' => $_POST['type'],
                        'ip' => $ip,
                        'datetime' => \Altum\Date::$date,
                    ]);

                    /* Update campaign statistics */
                    db()->where('campaign_id', $campaign_id)->update('campaigns', [$stat_table_column => db()->inc()]);
                }

                /* Flow tracking log */
                else if(isset($_POST['flow_id'])) {
                    $flow_id = (int) $_POST['flow_id'];

                    /* Insert subscriber log */
                    db()->insert('subscribers_logs', [
                        'subscriber_id' => $subscriber->subscriber_id,
                        'website_id' => $website->website_id,
                        'user_id' => $website->user_id,
                        'flow_id' => $flow_id,
                        'type' => $_POST['type'],
                        'ip' => $ip,
                        'datetime' => \Altum\Date::$date,
                    ]);

                    /* Update campaign statistics */
                    db()->where('flow_id', $flow_id)->update('flows', [$stat_table_column => db()->inc()]);
                }

                /* Update subscriber statistics */
                db()->where('subscriber_id', $subscriber->subscriber_id)->update('subscribers', [$stat_table_column => db()->inc()]);

                /* Update website statistics */
                db()->where('website_id', $website->website_id)->update('websites', [$stat_table_column => db()->inc()]);

                break;

            case 'permission_denied':

                /* Only track those stats if the user has the right plan settings */
                if(!$user->plan_settings->analytics_is_enabled) {
                    break;
                }

                /* Insert subscriber log */
                db()->insert('subscribers_logs', [
                    'website_id' => $website->website_id,
                    'user_id' => $website->user_id,
                    'type' => $_POST['type'],
                    'ip' => $ip,
                    'datetime' => \Altum\Date::$date,
                ]);

                break;
        }

    }

}
