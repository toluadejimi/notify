<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('flows') ?>"><?= l('campaigns.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('flow.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card d-flex flex-row mb-4">
        <div class="px-3 d-flex flex-column justify-content-center">
            <?php if($data->flow->is_enabled): ?>
                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-light" data-toggle="tooltip" title="<?= l('global.active') ?>">
                    <i class="fas fa-fw fa-sm fa-check text-success"></i>
                </div>
            <?php else: ?>
                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-light" data-toggle="tooltip" title="<?= l('global.disabled') ?>">
                    <i class="fas fa-fw fa-sm fa-pause text-danger"></i>
                </div>
            <?php endif ?>
        </div>

        <div class="card-body text-truncate d-flex justify-content-between align-items-center">
            <div class="text-truncate">
                <h1 class="h4 text-truncate mb-0"><?= sprintf(l('flow.header'), $data->flow->name) ?></h1>

                <div class="d-flex align-items-center">
                    <img src="<?= get_favicon_url_from_domain($data->website->host) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />

                    <a href="<?= url('website/' . $data->website->website_id) ?>" class="small text-muted text-truncate" data-toggle="tooltip" title="<?= $data->website->host . $data->website->path ?>">
                        <?= string_truncate($data->website->host . $data->website->path, 32) ?>
                    </a>
                </div>
            </div>

            <?= include_view(THEME_PATH . 'views/flows/flow_dropdown_button.php', ['id' => $data->flow->flow_id, 'resource_name' => $data->flow->name,]) ?>
        </div>
    </div>

    <div class="my-4">
        <div class="row">
            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= l('campaigns.last_sent_datetime') . ($data->flow->last_sent_datetime ? '<br />' . \Altum\Date::get($data->flow->last_sent_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->flow->last_sent_datetime, 3) . '</small>' : ' - ') ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                            <i class="fas fa-fw fa-sm fa-rocket text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->flow->last_sent_datetime ? \Altum\Date::get_timeago($data->flow->last_sent_datetime) : '-' ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($data->flow->datetime, 2) . '<br /><small>' . \Altum\Date::get($data->flow->datetime, 3) . '</small>') ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                            <i class="fas fa-fw fa-sm fa-clock text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->flow->datetime ? \Altum\Date::get_timeago($data->flow->datetime) : '-' ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($data->flow->last_datetime ? '<br />' . \Altum\Date::get($data->flow->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->flow->last_datetime, 3) . '</small>' : '-')) ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                            <i class="fas fa-fw fa-sm fa-clock-rotate-left text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->flow->last_datetime ? \Altum\Date::get_timeago($data->flow->last_datetime) : '-' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('campaigns.total_sent_push_notifications') ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-notification">
                            <i class="fas fa-fw fa-sm fa-fire text-notification"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->flow->total_sent_push_notifications) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('campaigns.segment') ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-subscriber">
                            <i class="fas fa-fw fa-sm fa-layer-group text-subscriber"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= l('campaigns.segment.' . $data->flow->segment) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('campaigns.total_displayed_push_notifications') ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                            <i class="fas fa-fw fa-sm fa-mobile text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->flow->total_displayed_push_notifications) . '/' . nr($data->flow->total_sent_push_notifications) ?>
                        <span class="text-muted">
                            <?= ' (' . nr(get_percentage_between_two_numbers($data->flow->total_displayed_push_notifications, $data->flow->total_sent_push_notifications)) . '%' . ')' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('campaigns.total_clicked_push_notifications') ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                            <i class="fas fa-fw fa-sm fa-mouse text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->flow->total_clicked_push_notifications) . '/' . nr($data->flow->total_displayed_push_notifications) ?>
                        <span class="text-muted">
                            <?= ' (' . nr(get_percentage_between_two_numbers($data->flow->total_clicked_push_notifications, $data->flow->total_displayed_push_notifications)) . '%' . ')' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('campaigns.total_closed_push_notifications') ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                            <i class="fas fa-fw fa-sm fa-times text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->flow->total_displayed_push_notifications) . '/' . nr($data->flow->total_displayed_push_notifications) ?>
                        <span class="text-muted">
                            <?= ' (' . nr(get_percentage_between_two_numbers($data->flow->total_displayed_push_notifications, $data->flow->total_displayed_push_notifications)) . '%' . ')' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-stream mr-1"></i> <?= l('subscriber.logs') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>

            <div class="ml-3">
                <a href="<?= url('subscribers-logs?flow_id=' . $data->flow->flow_id) ?>" class="btn btn-sm btn-primary-100" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-stream fa-sm"></i></a>
            </div>
        </div>

        <?php if(count($data->subscriber_logs)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.ip') ?></th>
                        <th><?= l('global.type') ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->subscriber_logs as $row): ?>

                        <tr>
                            <td class="text-nowrap">
                                <div>
                                    <?= $row->ip ?>
                                </div>

                                <div class="d-flex align-items-center">
                                    <img src="<?= get_favicon_url_from_domain($data->website->host) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />

                                    <a href="<?= url('website/' . $data->website->website_id) ?>" class="small text-muted" data-toggle="tooltip" title="<?= $data->website->host . $data->website->path ?>">
                                        <?= string_truncate($data->website->host . $data->website->path, 32) ?>
                                    </a>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <?= display_subscriber_log_type($row->type) ?>
                            </td>

                            <td class="text-nowrap">
                                <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($row->datetime, 1) ?>">
                                    <?= \Altum\Date::get_timeago($row->datetime) ?>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/subscribers-logs/subscriber_log_dropdown_button.php', ['id' => $row->subscriber_log_id, 'resource_name' => $row->ip]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>

                    </tbody>
                </table>
            </div>
        <?php else: ?>

            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                'filters_get' => $data->filters->get ?? [],
                'name' => 'subscribers_logs',
                'has_secondary_text' => true,
            ]); ?>

        <?php endif ?>
    </div>

    <div class="mt-4 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-tasks mr-1"></i> <?= l('flows.flow') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <tbody>
                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-heading fa-sm text-muted mr-1"></i>
                        <?= l('global.title') ?>
                    </td>
                    <td class="text-truncate">
                        <?= $data->flow->title ?>
                    </td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-paragraph fa-sm text-muted mr-1"></i>
                        <?= l('global.description') ?>
                    </td>
                    <td class="text-truncate">
                        <?= $data->flow->description ?>
                    </td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-link fa-sm text-muted mr-1"></i>
                        <?= l('global.url') ?>
                    </td>
                    <td class="text-truncate">
                        <?php if($data->flow->url): ?>
                            <?= $data->flow->url ?>
                            <a href="<?= $data->flow->url ?>" target="_blank" rel="nofollow noreferrer">
                                <i class="fas fa-fw fa-xs fa-external-link text-muted ml-1"></i>
                            </a>
                        <?php else: ?>
                            <?= l('global.none') ?>
                        <?php endif ?>
                    </td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-image fa-sm text-muted mr-1"></i>
                        <?= l('global.image') ?>
                    </td>
                    <td class="text-truncate">
                        <?php if($data->flow->image): ?>
                            <div>
                                <a href="<?= \Altum\Uploads::get_full_url('websites_flows_images') . $data->flow->image ?>" target="_blank" rel="nofollow noreferrer">
                                    <img src="<?= \Altum\Uploads::get_full_url('websites_flows_images') . $data->flow->image ?>" class="img-fluid rounded" style="max-width: 15rem;" loading="lazy" />
                                </a>
                            </div>
                        <?php else: ?>
                            <?= l('global.none') ?>
                        <?php endif ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true">
                <div class="px-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-mouse text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <div class="font-weight-bold text-muted small text-truncate"><?= sprintf(l('campaigns.button_x'), 1) ?></div>
                    <span>
                        <?php if($data->flow->settings->button_title_1): ?>
                            <?= $data->flow->settings->button_title_1 ?>
                            <a href="<?= $data->flow->settings->button_url_1 ?>" target="_blank" rel="nofollow noreferrer">
                                <i class="fas fa-fw fa-xs fa-external-link text-muted ml-1"></i>
                            </a>
                        <?php else: ?>
                            <?= l('global.no') ?>
                        <?php endif ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true">
                <div class="px-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-mouse text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <div class="font-weight-bold text-muted small text-truncate"><?= sprintf(l('campaigns.button_x'), 2) ?></div>
                    <span>
                        <?php if($data->flow->settings->button_title_2): ?>
                            <?= $data->flow->settings->button_title_2 ?>
                            <a href="<?= $data->flow->settings->button_url_2 ?>" target="_blank" rel="nofollow noreferrer">
                                    <i class="fas fa-fw fa-xs fa-external-link text-muted ml-1"></i>
                                </a>
                        <?php else: ?>
                            <?= l('global.no') ?>
                        <?php endif ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true">
                <div class="px-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm <?= $data->flow->settings->is_silent ? 'fa-volume-down' : 'fa-volume-up' ?> text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <div class="font-weight-bold text-muted small text-truncate"><?= l('campaigns.is_silent') ?></div>
                    <span><?= $data->flow->settings->is_silent ? l('global.yes') : l('global.no') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true">
                <div class="px-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm <?= $data->campaign->settings->is_auto_hide ? 'fa-eye-slash' : 'fa-eye' ?> text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <div class="font-weight-bold text-muted small text-truncate"><?= l('campaigns.is_auto_hide') ?></div>
                    <span><?= $data->campaign->settings->is_auto_hide ? l('global.yes') : l('global.no') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true">
                <div class="px-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-stopwatch text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <div class="font-weight-bold text-muted small text-truncate"><?= l('campaigns.ttl') ?></div>
                    <span><?= $data->notifications_ttl[$data->flow->settings->ttl] ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 p-3 text-truncate">
            <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true">
                <div class="px-3 d-flex flex-column justify-content-center">
                    <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-50">
                        <i class="fas fa-fw fa-sm fa-gauge-high text-muted"></i>
                    </div>
                </div>

                <div class="card-body text-truncate">
                    <div class="font-weight-bold text-muted small text-truncate"><?= l('campaigns.urgency') ?></div>
                    <span><?= l('campaigns.urgency.' . $data->flow->settings->urgency) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
