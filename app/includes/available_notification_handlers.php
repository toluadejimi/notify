<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

$available_notification_handlers = [
    'email' => [
        'icon' => 'fas fa-envelope',
        'emoji' => '📧',
        'color' => '#E57373',
        'background_color' => '#fef8f8',
    ],
    'webhook' => [
        'icon' => 'fas fa-satellite-dish',
        'emoji' => '📡',
        'color' => '#e73199',
        'background_color' => '#f9eef5',
    ],
    'slack' => [
        'icon' => 'fab fa-slack',
        'emoji' => '💼',
        'color' => '#9C27B0',
        'background_color' => '#faf2fb',
    ],
    'discord' => [
        'icon' => 'fab fa-discord',
        'emoji' => '🎧',
        'color' => '#7986CB',
        'background_color' => '#f8f9fc',
    ],
    'telegram' => [
        'icon' => 'fab fa-telegram',
        'emoji' => '💬',
        'color' => '#29B6F6',
        'background_color' => '#f5fbfe',
    ],
    'microsoft_teams' => [
        'icon' => 'fab fa-microsoft',
        'emoji' => '🔵',
        'color' => '#5C6BC0',
        'background_color' => '#f7f7fc',
    ],
    'twilio' => [
        'icon' => 'fas fa-sms',
        'emoji' => '📱',
        'color' => '#FFC107',
        'background_color' => '#fffcf3',
    ],
    'twilio_call' => [
        'icon' => 'fas fa-phone',
        'emoji' => '☎️',
        'color' => '#66BB6A',
        'background_color' => '#f7fcf7',
    ],
    'whatsapp' => [
        'icon' => 'fab fa-whatsapp',
        'emoji' => '🟢',
        'color' => '#4CAF50',
        'background_color' => '#fffcf3',
    ],
];

if(\Altum\Plugin::is_active('push-notifications') && settings()->push_notifications->is_enabled) {
    $available_notification_handlers['push_subscriber_id'] = [
        'icon' => 'fas fa-thumbtack',
        'emoji' => '📌',
        'color' => '#24ba7f',
        'background_color' => '#24ba7f',
    ];
}

return $available_notification_handlers;
