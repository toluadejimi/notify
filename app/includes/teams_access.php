<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

$access = [
    'read' => [
        'read.all' => l('global.all')
    ],

    'create' => [
        'create.websites' => l('websites.title'),
        'create.campaigns' => l('campaigns.title'),
        'create.segments' => l('segments.title'),
        'create.flows' => l('flows.title'),
        'create.notification_handlers' => l('notification_handlers.title'),
    ],

    'update' => [
        'update.websites' => l('websites.title'),
        'update.campaigns' => l('campaigns.title'),
        'update.segments' => l('segments.title'),
        'update.flows' => l('flows.title'),
        'update.notification_handlers' => l('notification_handlers.title'),
    ],

    'delete' => [
        'delete.websites' => l('websites.title'),
        'delete.subscribers' => l('subscribers.title'),
        'delete.subscribers_logs' => l('subscribers_logs.title'),
        'delete.campaigns' => l('campaigns.title'),
        'delete.segments' => l('segments.title'),
        'delete.flows' => l('flows.title'),
        'delete.notification_handlers' => l('notification_handlers.title'),
    ],
];

if(settings()->websites->domains_is_enabled) {
    $access['create']['create.domains'] = l('domains.title');
    $access['update']['update.domains'] = l('domains.title');
    $access['delete']['delete.domains'] = l('domains.title');
}

return $access;
