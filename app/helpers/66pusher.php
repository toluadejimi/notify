<?php

function display_subscriber_log_type($type) {
    return match ($type) {
        'subscribed' => '<span class="badge badge-success"><i class="fas fa-fw fa-sm fa-user-plus mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'unsubscribed' => '<span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-user-minus mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'permission_denied' => '<span class="badge badge-dark"><i class="fas fa-fw fa-sm fa-user-plus mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'displayed_notification' => '<span class="badge badge-light"><i class="fas fa-fw fa-sm fa-display mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'clicked_notification' => '<span class="badge badge-light"><i class="fas fa-fw fa-sm fa-mouse mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'closed_notification' => '<span class="badge badge-light"><i class="fas fa-fw fa-sm fa-times mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'manually_deleted' => '<span class="badge badge-warning"><i class="fas fa-fw fa-sm fa-trash-alt mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'push_notification_sent' => '<span class="badge bg-notification text-notification"><i class="fas fa-fw fa-sm fa-fire mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'expired_deleted' => '<span class="badge badge-danger"><i class="fas fa-fw fa-sm fa-calendar-times mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        'push_notification_failed' => '<span class="badge badge-dark"><i class="fas fa-fw fa-sm fa-window-close mr-1"></i> ' . l('subscribers.' . $type) . '</span>',
        default => $type,
    };
}
