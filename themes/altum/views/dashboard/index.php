<?php defined('ALTUMCODE') || die() ?>


<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="mb-3 d-flex justify-content-between">
        <div>
            <h1 class="h4 mb-0 text-truncate"><i class="fas fa-fw fa-xs fa-table-cells mr-1"></i> <?= l('dashboard.header') ?></h1>
        </div>
    </div>

    <div class="my-4">
        <div class="row">
            <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('websites') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-website">
                                <i class="fas fa-fw fa-sm fa-pager text-website"></i>
                            </div>
                        </a>
                    </div>

                    <div class="card-body text-truncate">
                        <?= sprintf(l('dashboard.total_websites'), '<span class="h6">' . nr($data->total_websites) . '</span>') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('subscribers') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-subscriber">
                                <i class="fas fa-fw fa-sm fa-user-check text-subscriber"></i>
                            </div>
                        </a>
                    </div>

                    <div class="card-body text-truncate">
                        <?= sprintf(l('dashboard.total_subscribers'), '<span class="h6">' . nr($data->total_subscribers) . '</span>') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= sprintf(l('dashboard.current_month_campaigns'), $data->usage->pusher_campaigns_current_month, ($this->user->plan_settings->campaigns_per_month_limit != -1 ? nr($this->user->plan_settings->campaigns_per_month_limit) : l('global.unlimited'))) ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('campaigns') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-campaign">
                                <i class="fas fa-fw fa-sm fa-rocket text-campaign"></i>
                            </div>
                        </a>
                    </div>

                    <div class="card-body text-truncate">
                        <?= sprintf(l('dashboard.total_campaigns'), '<span class="h6">' . nr($data->total_campaigns) . '</span>') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 p-3 position-relative text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= sprintf(l('dashboard.current_month_sent_push_notifications'), $data->usage->pusher_sent_push_notifications_current_month, ($this->user->plan_settings->sent_push_notifications_per_month_limit != -1 ? nr($this->user->plan_settings->sent_push_notifications_per_month_limit) : l('global.unlimited'))) ?>">
                    <div class="px-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('campaigns') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-notification">
                                <i class="fas fa-fw fa-sm fa-fire text-notification"></i>
                            </div>
                        </a>
                    </div>

                    <div class="card-body text-truncate">
                        <?= sprintf(l('dashboard.total_sent_push_notifications'), '<span class="h6">' . nr($data->total_sent_push_notifications) . '</span>') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($data->subscribers_logs_chart): ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="subscribers_logs_chart"></canvas>
                </div>
                <?php if(settings()->main->chart_cache ?? 12): ?>
                    <small class="text-muted"><i class="fas fa-fw fa-sm fa-info-circle mr-1"></i> <?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?></small>                <?php endif ?>
            </div>
        </div>

    <?php ob_start() ?>
        <script src="<?= ASSETS_FULL_URL . 'js/libraries/Chart.bundle.min.js?v=' . PRODUCT_CODE ?>"></script>
        <script src="<?= ASSETS_FULL_URL . 'js/chartjs_defaults.js?v=' . PRODUCT_CODE ?>"></script>

        <script>
            if(document.getElementById('subscribers_logs_chart')) {
                let css = window.getComputedStyle(document.body);
                let subscribed_color = css.getPropertyValue('--primary');
                let unsubscribed_color = css.getPropertyValue('--primary-100');
                let subscribed_color_gradient = null;
                let unsubscribed_color_gradient = null;

                /* Chart */
                let subscribers_logs_chart = document.getElementById('subscribers_logs_chart').getContext('2d');

                /* Colors */
                subscribed_color_gradient = subscribers_logs_chart.createLinearGradient(0, 0, 0, 250);
                subscribed_color_gradient.addColorStop(0, set_hex_opacity(subscribed_color, 0.6));
                subscribed_color_gradient.addColorStop(1, set_hex_opacity(subscribed_color, 0.1));

                unsubscribed_color_gradient = subscribers_logs_chart.createLinearGradient(0, 0, 0, 250);
                unsubscribed_color_gradient.addColorStop(0, set_hex_opacity(unsubscribed_color, 0.6));
                unsubscribed_color_gradient.addColorStop(1, set_hex_opacity(unsubscribed_color, 0.1));

                new Chart(subscribers_logs_chart, {
                    type: 'line',
                    data: {
                        labels: <?= $data->subscribers_logs_chart['labels'] ?? '[]' ?>,
                        datasets: [
                            {
                                label: <?= json_encode(l('websites.subscribed')) ?>,
                                data: <?= $data->subscribers_logs_chart['subscribed'] ?? '[]' ?>,
                                backgroundColor: subscribed_color_gradient,
                                borderColor: subscribed_color,
                                fill: true
                            },
                            {
                                label: <?= json_encode(l('websites.unsubscribed')) ?>,
                                data: <?= $data->subscribers_logs_chart['unsubscribed'] ?? '[]' ?>,
                                backgroundColor: unsubscribed_color_gradient,
                                borderColor: unsubscribed_color,
                                fill: true
                            }
                        ]
                    },
                    options: chart_options
                });
            }
        </script>
        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
    <?php endif ?>

    <div class="mt-4 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-pager mr-1 text-website"></i> <?= l('dashboard.websites_header') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>

            <div class="ml-3">
                <a href="<?= url('website-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('websites.create') ?></a>
                <a href="<?= url('websites') ?>" class="btn btn-sm bg-website text-website" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-pager fa-sm"></i></a>
            </div>
        </div>

        <?php if(count($data->websites)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.name') ?></th>
                        <th><?= l('websites.subscribers') ?></th>
                        <th><?= l('campaigns.notifications') ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php $i = 1; ?>
                    <?php foreach($data->websites as $row): ?>
                        <?php if($i > 5) break; $i++ ?>

                        <tr>
                            <td class="text-nowrap">
                                <div>
                                    <a href="<?= url('website/' . $row->website_id) ?>"><?= $row->name ?></a>
                                </div>

                                <div class="d-flex align-items-center">
                                    <img src="<?= get_favicon_url_from_domain($row->host) ?>" class="img-fluid icon-favicon mr-1" loading="lazy" />

                                    <span class="small text-muted"><?= $row->host . $row->path ?></span>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <a href="<?= url('subscribers?website_id=' . $row->website_id) ?>" class="badge text-subscriber bg-subscriber">
                                    <i class="fas fa-fw fa-sm fa-user-check mr-1"></i> <?= nr($row->total_subscribers) ?>
                                </a>
                            </td>

                            <td class="text-nowrap">
                                <a href="<?= url('campaigns?website_id=' . $row->website_id) ?>" class="badge text-notification bg-notification">
                                    <i class="fas fa-fw fa-sm fa-fire mr-1"></i> <?= nr($row->total_sent_push_notifications) ?>
                                </a>
                            </td>

                            <td class="text-nowrap">
                                <div>
                                    <?php if($row->is_enabled == 1): ?>
                                        <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-check mr-1"></i> <?= l('global.active') ?></span>
                                    <?php elseif($row->is_enabled == 0): ?>
                                        <span class="badge badge-warning"><i class="fas fa-fw fa-sm fa-eye-slash mr-1"></i> <?= l('global.disabled') ?></span>
                                    <?php endif ?>
                                </div>
                            </td>

                            <td class="text-nowrap text-muted">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' : '-')) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/websites/website_dropdown_button.php', ['id' => $row->website_id, 'resource_name' => $row->name, 'host' => $row->host, 'path' => $row->path, 'pixel_key' => $row->pixel_key, 'domain_id' => $row->domain_id, 'domains' => $data->domains]) ?>
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
                'name' => 'websites',
                'has_secondary_text' => true,
            ]); ?>

        <?php endif ?>
    </div>

    <div class="mt-4 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-user-check mr-1 text-subscriber"></i> <?= l('dashboard.subscribers_header') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>

            <div class="ml-3">
                <a href="<?= url('subscribers') ?>" class="btn btn-sm bg-subscriber text-subscriber" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-user-check fa-sm"></i></a>
            </div>
        </div>

        <?php if(count($data->subscribers)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('websites.subscriber') ?></th>
                        <th><?= l('global.device') ?></th>
                        <th><?= l('campaigns.notifications') ?></th>
                        <th><?= l('global.details') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->subscribers as $row): ?>

                        <tr>
                            <td class="text-nowrap">
                                <div>
                                    <a href="<?= url('subscriber/' . $row->subscriber_id) ?>">
                                        <?= $row->ip ?>
                                    </a>
                                </div>

                                <div class="d-flex align-items-center">
                                    <img src="<?= get_favicon_url_from_domain($data->websites[$row->website_id]->host) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />

                                    <a href="<?= url('website/' . $row->website_id) ?>" class="small text-muted" data-toggle="tooltip" title="<?= $data->websites[$row->website_id]->host . $data->websites[$row->website_id]->path ?>">
                                        <?= string_truncate($data->websites[$row->website_id]->host . $data->websites[$row->website_id]->path, 32) ?>
                                    </a>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                 <span class="badge badge-light">
                                    <?= $row->device_type ? '<i class="fas fa-fw fa-sm fa-' . $row->device_type . ' mr-1"></i>' . l('global.device.' . $row->device_type) : l('global.unknown') ?>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge text-notification bg-notification">
                                    <i class="fas fa-fw fa-sm fa-fire mr-1"></i> <?= nr($row->total_sent_push_notifications) ?>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <?php if(($row->custom_parameters = json_decode($row->custom_parameters ?? '', true)) && count($row->custom_parameters)): ?>
                                        <?php ob_start() ?>
                                        <div class='d-flex flex-column p-3 text-left'>
                                            <div class='d-flex flex-column my-1'>
                                                <strong><?= sprintf(l('subscribers.custom_parameters'), count($row->custom_parameters)) ?></strong>
                                            </div>

                                            <?php foreach($row->custom_parameters as $key => $value): ?>
                                                <div class='d-flex flex-column my-1'>
                                                    <div><?= e($key) ?></div>
                                                    <strong><?= e($value) ?></strong>
                                                </div>
                                            <?php endforeach ?>
                                        </div>

                                        <?php $tooltip = ob_get_clean() ?>

                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                            <i class="fas fa-fw fa-fingerprint text-primary"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('subscribers.custom_parameters'), 0) ?>">
                                            <i class="fas fa-fw fa-fingerprint text-muted"></i>
                                        </span>
                                    <?php endif ?>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('campaigns.last_sent_datetime') . ($row->last_sent_datetime ? '<br />' . \Altum\Date::get($row->last_sent_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_sent_datetime, 3) . '</small>' : ' - ') ?>">
                                        <i class="fas fa-fw fa-rocket text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>') ?>">
                                        <i class="fas fa-fw fa-clock text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' : '-')) ?>">
                                        <i class="fas fa-fw fa-history text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('subscribers.subscribed_on_url') . '<br />' . $row->subscribed_on_url ?>">
                                        <i class="fas fa-fw fa-link text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" title="<?= get_continent_from_continent_code($row->continent_code ?? l('global.unknown')) ?>">
                                        <i class="fas fa-fw fa-globe-europe text-muted"></i>
                                    </span>

                                    <?php if($row->country_code): ?>
                                        <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($row->country_code) . '.svg' ?>" class="icon-favicon mr-2" data-toggle="tooltip" title="<?= get_country_from_country_code($row->country_code) ?>" />
                                    <?php else: ?>
                                        <span class="mr-2" data-toggle="tooltip" title="<?= l('global.unknown') ?>">
                                            <i class="fas fa-fw fa-flag text-muted"></i>
                                        </span>
                                    <?php endif ?>

                                    <span class="mr-2" data-toggle="tooltip" title="<?= $row->city_name ?? l('global.unknown') ?>">
                                        <i class="fas fa-fw fa-city text-muted"></i>
                                    </span>

                                    <img src="<?= ASSETS_FULL_URL . 'images/os/' . os_name_to_os_key($row->os_name) . '.svg' ?>" class="img-fluid icon-favicon mr-2" data-toggle="tooltip" title="<?= $row->os_name ?: l('global.unknown') ?>" />

                                    <img src="<?= ASSETS_FULL_URL . 'images/browsers/' . browser_name_to_browser_key($row->browser_name) . '.svg' ?>" class="img-fluid icon-favicon mr-2" data-toggle="tooltip" title="<?= $row->browser_name ?: l('global.unknown') ?>" />
                                </div>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/subscribers/subscriber_dropdown_button.php', ['id' => $row->subscriber_id, 'resource_name' => $row->ip, 'website_id' => $row->website_id]) ?>
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
                'name' => 'websites',
                'has_secondary_text' => true,
            ]); ?>

        <?php endif ?>

    </div>

    <div class="mt-4 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-rocket mr-1 text-campaign"></i> <?= l('dashboard.campaigns_header') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>

            <div class="ml-3">
                <a href="<?= url('campaign-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('campaigns.create') ?></a>
                <a href="<?= url('campaigns') ?>" class="btn btn-sm bg-campaign text-campaign" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-rocket fa-sm"></i></a>
            </div>
        </div>

        <?php if(count($data->campaigns)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('campaigns.campaign') ?></th>
                        <th><?= l('campaigns.segment') ?></th>
                        <th><?= l('campaigns.notifications') ?></th>
                        <th><?= l('global.status') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->campaigns as $row): ?>

                        <tr>
                            <td class="text-nowrap">
                                <div>
                                    <?php if(in_array($row->status, ['draft', 'scheduled'])): ?>
                                        <a href="<?= url('campaign-update/' . $row->campaign_id) ?>"><?= $row->name ?></a>
                                    <?php elseif($row->status == 'processing'): ?>
                                        <?= $row->name ?>
                                    <?php elseif($row->status == 'sent'): ?>
                                        <a href="<?= url('campaign/' . $row->campaign_id) ?>"><?= $row->name ?></a>
                                    <?php endif ?>
                                </div>

                                <div class="d-flex align-items-center">
                                    <img src="<?= get_favicon_url_from_domain($data->websites[$row->website_id]->host) ?>" class="img-fluid icon-favicon-small mr-1" loading="lazy" />

                                    <a href="<?= url('website/' . $row->website_id) ?>" class="small text-muted" data-toggle="tooltip" title="<?= $data->websites[$row->website_id]->host . $data->websites[$row->website_id]->path ?>">
                                        <?= string_truncate($data->websites[$row->website_id]->host . $data->websites[$row->website_id]->path, 32) ?>
                                    </a>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <?php if(is_numeric($row->segment)): ?>
                                    <a href="<?= url('segment-update/' . $row->segment) ?>" class="badge badge-light">
                                        <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.saved') ?>
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-light">
                                        <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.' . $row->segment) ?>
                                    </span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge text-notification bg-notification" data-toggle="tooltip" title="<?= nr(get_percentage_between_two_numbers($row->total_sent_push_notifications, $row->total_push_notifications)) . '%' ?>">
                                    <i class="fas fa-fw fa-sm fa-fire mr-1"></i> <?= nr($row->total_sent_push_notifications) . '/' . nr($row->total_push_notifications) ?>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->status == 'draft'): ?>
                                    <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-save mr-1"></i> <?= l('campaigns.status.draft') ?></span>
                                <?php elseif($row->status == 'scheduled'): ?>
                                    <span class="badge badge-gray-300" data-toggle="tooltip" title="<?= \Altum\Date::get_time_until($row->scheduled_datetime) ?>"><i class="fas fa-fw fa-sm fa-calendar-day mr-1"></i> <?= l('campaigns.status.scheduled') ?></span>
                                <?php elseif($row->status == 'processing'): ?>
                                    <span class="badge badge-warning"><i class="fas fa-fw fa-sm fa-spinner fa-spin mr-1"></i> <?= l('campaigns.status.processing') ?></span>
                                <?php elseif($row->status == 'sent'): ?>
                                    <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-check mr-1"></i> <?= l('campaigns.status.sent') ?></span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('campaigns.scheduled_datetime') . ($row->scheduled_datetime && $row->settings->is_scheduled ? '<br />' . \Altum\Date::get($row->scheduled_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->scheduled_datetime, 3) . '</small>' : ' - ') ?>">
                                        <i class="fas fa-fw fa-calendar-day text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('campaigns.last_sent_datetime') . ($row->last_sent_datetime ? '<br />' . \Altum\Date::get($row->last_sent_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_sent_datetime, 3) . '</small>' : ' - ') ?>">
                                        <i class="fas fa-fw fa-rocket text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>') ?>">
                                        <i class="fas fa-fw fa-clock text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' : '-')) ?>">
                                        <i class="fas fa-fw fa-history text-muted"></i>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/campaigns/campaign_dropdown_button.php', ['id' => $row->campaign_id, 'resource_name' => $row->name,]) ?>
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
                'name' => 'campaigns',
                'has_secondary_text' => true,
            ]); ?>

        <?php endif ?>

    </div>

</div>
