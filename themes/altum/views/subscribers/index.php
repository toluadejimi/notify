<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-user-check mr-1"></i> <?= l('subscribers.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('subscribers.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex d-print-none">
            <div class="">
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= count($data->subscribers) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('subscribers?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('subscribers?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                        <a href="#" onclick="window.print();return false;" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= count($data->subscribers) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.filters.header') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if($data->filters->has_applied_filters): ?>
                                <a href="<?= url(\Altum\Router::$original_request) ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="search" class="small"><?= l('global.filters.search') ?></label>
                                <input type="search" name="search" id="search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                <select name="search_by" id="filters_search_by" class="custom-select custom-select-sm">
                                    <option value="ip" <?= $data->filters->search_by == 'ip' ? 'selected="selected"' : null ?>><?= l('global.ip') ?></option>
                                    <option value="city_name" <?= $data->filters->search_by == 'city_name' ? 'selected="selected"' : null ?>><?= l('global.city') ?></option>
                                    <option value="os_name" <?= $data->filters->search_by == 'os_name' ? 'selected="selected"' : null ?>><?= l('global.os_name') ?></option>
                                    <option value="browser_name" <?= $data->filters->search_by == 'browser_name' ? 'selected="selected"' : null ?>><?= l('global.browser_name') ?></option>
                                    <option value="browser_language" <?= $data->filters->search_by == 'browser_language' ? 'selected="selected"' : null ?>><?= l('global.browser_language') ?></option>
                                    <option value="subscribed_on_url" <?= $data->filters->search_by == 'subscribed_on_url' ? 'selected="selected"' : null ?>><?= l('subscribers.subscribed_on_url') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_website_id" class="small"><?= l('websites.website') ?></label>
                                <select name="website_id" id="filters_website_id" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->websites as $website_id => $website): ?>
                                        <option value="<?= $website_id ?>" <?= isset($data->filters->filters['website_id']) && $data->filters->filters['website_id'] == $website_id ? 'selected="selected"' : null ?>><?= $website->name . ' - ' . $website->host . $website->path ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_device_type" class="small"><?= l('global.device') ?></label>
                                <select name="device_type" id="filters_device_type" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach(['desktop', 'tablet', 'mobile'] as $device_type): ?>
                                        <option value="<?= $device_type ?>" <?= isset($data->filters->filters['device_type']) && $data->filters->filters['device_type'] == $device_type ? 'selected="selected"' : null ?>><?= l('global.device.' . $device_type) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_continent_code" class="small"><?= l('global.continent') ?></label>
                                <select name="continent_code" id="filters_continent_code" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach(get_continents_array() as $continent_code => $continent_name): ?>
                                        <option value="<?= $continent_code ?>" <?= isset($data->filters->filters['continent_code']) && $data->filters->filters['continent_code'] == $continent_code ? 'selected="selected"' : null ?>><?= $continent_name ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_country" class="small"><?= l('global.country') ?></label>
                                <select name="country_code" id="filters_country_code" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach(get_countries_array() as $country_code => $country_name): ?>
                                        <option value="<?= $country_code ?>" <?= isset($data->filters->filters['country_code']) && $data->filters->filters['country_code'] == $country_code ? 'selected="selected"' : null ?>><?= $country_name ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="last_sent_datetime" <?= $data->filters->order_by == 'last_sent_datetime' ? 'selected="selected"' : null ?>><?= l('campaigns.last_sent_datetime') ?></option>
                                    <option value="total_sent_push_notifications" <?= $data->filters->order_by == 'total_sent_push_notifications' ? 'selected="selected"' : null ?>><?= l('campaigns.total_sent_push_notifications') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="order_type" class="custom-select custom-select-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="results_per_page" class="custom-select custom-select-sm">
                                    <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                        <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4 mt-4">
                                <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div class="ml-3">
                <button id="bulk_enable" type="button" class="btn btn-light <?= count($data->subscribers) ? null : 'disabled' ?>" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

                <div id="bulk_group" class="btn-group d-none" role="group">
                    <div class="btn-group dropdown" role="group">
                        <button id="bulk_actions" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                            <?= l('global.bulk_actions') ?> <span id="bulk_counter" class="d-none"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="bulk_actions">
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#bulk_delete_modal"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                        </div>
                    </div>

                    <button id="bulk_disable" type="button" class="btn btn-secondary" data-toggle="tooltip" title="<?= l('global.close') ?>"><i class="fas fa-fw fa-times"></i></button>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($data->subscribers)): ?>
        <form id="table" action="<?= SITE_URL . 'subscribers/bulk' ?>" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
            <input type="hidden" name="type" value="" data-bulk-type />
            <input type="hidden" name="original_request" value="<?= base64_encode(\Altum\Router::$original_request) ?>" />
            <input type="hidden" name="original_request_query" value="<?= base64_encode(\Altum\Router::$original_request_query) ?>" />

            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th data-bulk-table class="d-none">
                            <div class="custom-control custom-checkbox">
                                <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                <label class="custom-control-label" for="bulk_select_all"></label>
                            </div>
                        </th>
                        <th><?= l('websites.subscriber') ?></th>
                        <th><?= l('global.device') ?></th>
                        <th><?= l('campaigns.notifications') ?></th>
                        <th><?= l('global.details') ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->subscribers as $row): ?>

                        <tr>
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_subscriber_id_<?= $row->subscriber_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->subscriber_id ?>" />
                                    <label class="custom-control-label" for="selected_subscriber_id_<?= $row->subscriber_id ?>"></label>
                                </div>
                            </td>

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
        </form>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>
        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get ?? [],
            'name' => 'subscribers',
            'has_secondary_text' => true,
        ]); ?>
    <?php endif ?>
</div>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
