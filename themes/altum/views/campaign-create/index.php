<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('campaigns') ?>"><?= l('campaigns.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('campaign_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-rocket mr-1"></i> <?= l('campaign_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form id="form" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="website_id"><i class="fas fa-fw fa-sm fa-pager text-muted mr-1"></i> <?= l('websites.website') ?></label>
                    <select id="website_id" name="website_id" class="form-control <?= \Altum\Alerts::has_field_errors('website_id') ? 'is-invalid' : null ?>" required="required">
                        <?php foreach($data->websites as $website): ?>
                            <option value="<?= $website->website_id ?>" <?= $data->values['website_id'] == $website->website_id ? 'selected="selected"' : null ?>><?= $website->name . ' - ' . $website->host . $website->path ?></option>
                        <?php endforeach ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('website_id') ?>
                </div>

                <div class="form-group">
                    <label for="title"><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('global.title') ?></label>
                    <input type="text" id="title" name="title" class="form-control <?= \Altum\Alerts::has_field_errors('title') ? 'is-invalid' : null ?>" value="<?= $data->values['title'] ?>" maxlength="128" required="required" />
                    <?= \Altum\Alerts::output_field_error('title') ?>
                    <small class="form-text text-muted"><?= l('campaigns.title_help') ?></small>
                    <small class="form-text text-muted"><?= l('campaigns.variables') ?></small>
                    <small class="form-text text-muted"><?= l('global.admin_spintax_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-fw fa-sm fa-paragraph text-muted mr-1"></i> <?= l('global.description') ?></label>
                    <input type="text" id="description" name="description" value="<?= $data->values['description'] ?>" class="form-control <?= \Altum\Alerts::has_field_errors('description') ? 'is-invalid' : null ?>" maxlength="128" required="required" />
                    <?= \Altum\Alerts::output_field_error('description') ?>
                    <small class="form-text text-muted"><?= l('campaigns.description_help') ?></small>
                    <small class="form-text text-muted"><?= l('campaigns.variables') ?></small>
                    <small class="form-text text-muted"><?= l('global.admin_spintax_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('global.url') ?></label>
                    <input type="url" id="url" name="url" value="<?= $data->values['url'] ?>" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" maxlength="512" placeholder="<?= l('global.url_placeholder') ?>" />
                    <?= \Altum\Alerts::output_field_error('url') ?>
                </div>

                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->websites->campaign_image_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->websites->campaign_image_size_limit) ?>">
                    <label for="image"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('global.image') ?></label>
                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'websites_campaigns_images', 'file_key' => 'image', 'already_existing_image' => null]) ?>
                    <?= \Altum\Alerts::output_field_error('image') ?>
                    <small class="form-text text-muted"><?= l('campaigns.image_help') ?> <?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('websites_campaigns_images')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->websites->campaign_image_size_limit) ?></small>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-column flex-xl-row justify-content-between">
                        <label for="segment"><i class="fas fa-fw fa-sm fa-layer-group text-muted mr-1"></i> <?= l('campaigns.segment') ?> <span id="segment_count"></span></label>
                        <a href="<?= url('segment-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('segments.create') ?></a>
                    </div>
                    <select id="segment" name="segment" class="form-control <?= \Altum\Alerts::has_field_errors('segment') ? 'is-invalid' : null ?>" required="required">
                        <option value="all" <?= $data->values['segment'] == 'all' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.all') ?></option>
                        <option value="custom" <?= $data->values['segment'] == 'custom' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.custom') ?></option>
                        <option value="filter" <?= $data->values['segment'] == 'filter' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.filter') ?></option>
                        <?php if(count($data->segments)): ?>
                            <optgroup label="<?= l('campaigns.segment.saved') ?>">
                                <?php foreach ($data->segments as $segment): ?>
                                    <option value="<?= $segment->segment_id ?>" <?= $data->values['segment'] == $segment->segment_id ? 'selected="selected"' : null ?> data-website-id="<?= $segment->website_id ?>"><?= $segment->name ?></option>
                                <?php endforeach ?>
                            </optgroup>
                        <?php endif ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('segment') ?>
                </div>

                <div class="form-group" data-segment="custom">
                    <label for="subscribers_ids"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('campaigns.subscribers_ids') ?></label>
                    <input type="text" id="subscribers_ids" name="subscribers_ids" value="<?= $data->values['subscribers_ids'] ?>" class="form-control <?= \Altum\Alerts::has_field_errors('subscribers_ids') ? 'is-invalid' : null ?>" placeholder="<?= l('campaigns.subscribers_ids_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('subscribers_ids') ?>
                    <small class="form-text text-muted"><?= l('campaigns.subscribers_ids_help') ?></small>
                </div>

                <div class="form-group" data-segment="filter">
                    <div class="form-group">
                        <label for="filters_continents"><i class="fas fa-fw fa-sm fa-globe-europe text-muted mr-1"></i> <?= l('global.continents') ?></label>
                        <select id="filters_continents" name="filters_continents[]" class="custom-select" multiple="multiple">
                            <?php foreach(get_continents_array() as $continent_code => $continent_name): ?>
                                <option value="<?= $continent_code ?>" <?= in_array($continent_code,$data->values['filters_continents'] ?? []) ? 'selected="selected"' : null ?>><?= $continent_name ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-segment="filter">
                    <div class="form-group">
                        <label for="filters_countries"><i class="fas fa-fw fa-sm fa-flag text-muted mr-1"></i> <?= l('global.countries') ?></label>
                        <select id="filters_countries" name="filters_countries[]" class="custom-select" multiple="multiple">
                            <?php foreach(get_countries_array() as $key => $value): ?>
                                <option value="<?= $key ?>" <?= in_array($key, $data->values['filters_countries'] ?? []) ? 'selected="selected"' : null ?>><?= $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-type="filter">
                    <label for="filters_cities"><i class="fas fa-fw fa-sm fa-city text-muted mr-1"></i> <?= l('global.cities') ?></label>
                    <input type="text" id="filters_cities" name="filters_cities" value="<?= $data->values['filters_cities'] ?>" class="form-control" placeholder="<?= l('segments.cities_placeholder') ?>" />
                    <?= \Altum\Alerts::output_field_error('filters_cities') ?>
                    <small class="form-text text-muted"><?= l('segments.cities_help') ?></small>
                </div>

                <div class="form-group" data-segment="filter">
                    <label for="device_type"><i class="fas fa-fw fa-sm fa-laptop text-muted mr-1"></i> <?= l('global.device') ?></label>
                    <div class="row">
                        <?php foreach(['desktop', 'tablet', 'mobile'] as $device_type): ?>
                            <div class="col-12 col-md-4 mb-3">
                                <div class="custom-control custom-switch">
                                    <input id="<?= 'filters_device_type###' . $device_type ?>" name="filters_device_type[]" value="<?= $device_type ?>" type="checkbox" class="custom-control-input" <?= in_array($device_type, $data->values['filters_device_type'] ?? []) ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="<?= 'filters_device_type###' . $device_type ?>"><?= l('global.device.' . $device_type) ?></label>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <div class="form-group" data-segment="filter">
                    <label for="filters_operating_systems"><i class="fas fa-fw fa-server fa-sm text-muted mr-1"></i> <?= l('segments.operating_systems') ?></label>
                    <select id="filters_operating_systems" name="filters_operating_systems[]" class="custom-select" multiple="multiple">
                        <?php foreach(['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS'] as $os_name): ?>
                            <option value="<?= $os_name ?>" <?= in_array($os_name, $data->values['filters_operating_systems'] ?? []) ? 'selected="selected"' : null ?>><?= $os_name ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group" data-segment="filter">
                    <label for="filters_browsers"><i class="fas fa-fw fa-window-restore fa-sm text-muted mr-1"></i> <?= l('segments.browsers') ?></label>
                    <select id="filters_browsers" name="filters_browsers[]" class="custom-select" multiple="multiple">
                        <?php foreach(['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet'] as $browser_name): ?>
                            <option value="<?= $browser_name ?>" <?= in_array($browser_name, $data->values['filters_browsers'] ?? []) ? 'selected="selected"' : null ?>><?= $browser_name ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group" data-segment="filter">
                    <label for="filters_languages"><i class="fas fa-fw fa-language fa-sm text-muted mr-1"></i> <?= l('segments.languages') ?></label>
                    <select id="filters_languages" name="filters_languages[]" class="custom-select" multiple="multiple">
                        <?php foreach(get_locale_languages_array() as $locale => $language): ?>
                            <option value="<?= $locale ?>" <?= in_array($locale, $data->values['filters_languages'] ?? []) ? 'selected="selected"' : null ?>><?= $language ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#buttons_container" aria-expanded="false" aria-controls="buttons_container">
                    <i class="fas fa-fw fa-mouse fa-sm mr-1"></i> <?= l('campaigns.buttons') ?>
                </button>

                <div class="collapse" id="buttons_container">
                    <div class="alert alert-info">
                        <i class="fas fa-fw fa-sm fa-info-circle mr-2"></i> <?= l('campaigns.buttons_info') ?>
                    </div>

                    <h2 class="h6"><?= sprintf(l('campaigns.button_x'), 1) ?></h2>

                    <div class="p-3 bg-gray-50 rounded mb-4">
                        <div class="form-group">
                            <label for="button_title_1"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.title') ?></label>
                            <input type="text" id="button_title_1" name="button_title_1" class="form-control <?= \Altum\Alerts::has_field_errors('button_title_1') ? 'is-invalid' : null ?>" value="<?= $data->values['button_title_1'] ?>" maxlength="16" />
                            <?= \Altum\Alerts::output_field_error('button_title_1') ?>
                        </div>

                        <div class="form-group">
                            <label for="button_url_1"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('global.url') ?></label>
                            <input type="url" id="button_url_1" name="button_url_1" class="form-control <?= \Altum\Alerts::has_field_errors('button_url_1') ? 'is-invalid' : null ?>" value="<?= $data->values['button_url_1'] ?>" maxlength="512" placeholder="<?= l('global.url_placeholder') ?>" />
                            <?= \Altum\Alerts::output_field_error('button_url_1') ?>
                        </div>
                    </div>

                    <h2 class="h6"><?= sprintf(l('campaigns.button_x'), 2) ?></h2>

                    <div class="p-3 bg-gray-50 rounded mb-4">
                        <div class="form-group">
                            <label for="button_title_2"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.title') ?></label>
                            <input type="text" id="button_title_2" name="button_title_2" class="form-control <?= \Altum\Alerts::has_field_errors('button_title_2') ? 'is-invalid' : null ?>" value="<?= $data->values['button_title_2'] ?>" maxlength="16" />
                            <?= \Altum\Alerts::output_field_error('button_title_2') ?>
                        </div>

                        <div class="form-group">
                            <label for="button_url_2"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('global.url') ?></label>
                            <input type="url" id="button_url_2" name="button_url_2" class="form-control <?= \Altum\Alerts::has_field_errors('button_url_2') ? 'is-invalid' : null ?>" value="<?= $data->values['button_url_2'] ?>" maxlength="512" placeholder="<?= l('global.url_placeholder') ?>" />
                            <?= \Altum\Alerts::output_field_error('button_url_2') ?>
                        </div>
                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#scheduling_container" aria-expanded="false" aria-controls="scheduling_container">
                    <i class="fas fa-fw fa-calendar-day fa-sm mr-1"></i> <?= l('campaigns.scheduling') ?>
                </button>

                <div class="collapse" id="scheduling_container">
                    <div class="form-group custom-control custom-switch">
                        <input
                                id="is_scheduled"
                                name="is_scheduled"
                                type="checkbox"
                                class="custom-control-input"
                            <?= $data->values['is_scheduled'] && !empty($data->values['scheduled_datetime']) ? 'checked="checked"' : null ?>
                        >
                        <label class="custom-control-label" for="is_scheduled"><?= l('campaigns.is_scheduled') ?></label>
                    </div>

                    <div id="is_scheduled_container" class="d-none">
                        <div class="form-group">
                            <label for="scheduled_datetime"><i class="fas fa-fw fa-calendar-day fa-sm text-muted mr-1"></i> <?= l('campaigns.scheduled_datetime') ?></label>
                            <input
                                    id="scheduled_datetime"
                                    type="text"
                                    class="form-control"
                                    name="scheduled_datetime"
                                    value="<?= (new \DateTime($data->values['scheduled_datetime'], new \DateTimeZone(\Altum\Date::$default_timezone)))->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s'); ?>"
                                    placeholder="<?= l('campaigns.scheduled_datetime') ?>"
                                    autocomplete="off"
                                    data-daterangepicker
                            />
                        </div>
                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('campaigns.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group custom-control custom-switch">
                        <input id="is_silent" name="is_silent" type="checkbox" class="custom-control-input" <?= $data->values['is_silent'] ? 'checked="checked"' : null?>>
                        <label class="custom-control-label" for="is_silent"><?= l('campaigns.is_silent') ?></label>
                        <small class="form-text text-muted"><?= l('campaigns.is_silent_help') ?></small>
                    </div>

                    <div class="form-group custom-control custom-switch">
                        <input id="is_auto_hide" name="is_auto_hide" type="checkbox" class="custom-control-input" <?= $data->values['is_auto_hide'] ? 'checked="checked"' : null?>>
                        <label class="custom-control-label" for="is_auto_hide"><?= l('campaigns.is_auto_hide') ?></label>
                        <small class="form-text text-muted"><?= l('campaigns.is_auto_hide_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="ttl"><i class="fas fa-fw fa-sm fa-stopwatch text-muted mr-1"></i> <?= l('campaigns.ttl') ?></label>
                        <select id="ttl" name="ttl" class="form-control <?= \Altum\Alerts::has_field_errors('ttl') ? 'is-invalid' : null ?>" required="required">
                            <?php foreach($data->notifications_ttl as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $data->values['ttl'] == $key ? 'selected="selected"' : null ?>><?= $value ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= \Altum\Alerts::output_field_error('ttl') ?>
                        <small class="form-text text-muted"><?= l('campaigns.ttl_help') ?></small>
                        <small class="form-text text-muted"><?= l('campaigns.ttl_help2') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="urgency"><i class="fas fa-fw fa-sm fa-tachometer-alt text-muted mr-1"></i> <?= l('campaigns.urgency') ?></label>
                        <select id="urgency" name="urgency" class="form-control <?= \Altum\Alerts::has_field_errors('urgency') ? 'is-invalid' : null ?>" required="required">
                            <?php foreach(['low', 'normal', 'high'] as $key): ?>
                                <option value="<?= $key ?>" <?= $data->values['urgency'] == $key ? 'selected="selected"' : null ?>><?= l('campaigns.urgency.' . $key) ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= \Altum\Alerts::output_field_error('urgency') ?>
                        <small class="form-text text-muted"><?= l('campaigns.urgency_help') ?></small>
                    </div>
                </div>

                <button type="submit" name="save" class="btn btn-sm btn-block btn-outline-primary mt-4"><?= l('campaigns.save') ?></button>
                <button type="submit" name="send" class="btn btn-block btn-primary mt-3"><?= l('campaigns.send') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    /* Schedule */
    let schedule_handler = () => {
        if (document.querySelector('#is_scheduled').checked) {
            document.querySelector('#is_scheduled_container').classList.remove('d-none');
        } else {
            document.querySelector('#is_scheduled_container').classList.add('d-none');
        }
    };

    document.querySelector('#is_scheduled').addEventListener('change', schedule_handler);

    schedule_handler();

    /* Daterangepicker */
    let locale = <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>;
    $('[data-daterangepicker]').daterangepicker({
        minDate: new Date(),
        alwaysShowCalendars: true,
        singleCalendar: true,
        singleDatePicker: true,
        locale: {...locale, format: 'YYYY-MM-DD HH:mm:ss'},
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
    }, (start, end, label) => {});

    type_handler('[name="segment"]', 'data-segment');
    document.querySelector('[name="segment"]') && document.querySelectorAll('[name="segment"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="segment"]', 'data-segment'); }));


    document.querySelector('#website_id').addEventListener('change', async event => {
        await get_segment_count();
        process_segments();
    });

    document.querySelector('#segment').addEventListener('change', async event => {
        await get_segment_count();
    });

    document.querySelectorAll('[name^="filters_"]').forEach(element => element.addEventListener('change', async event => {
        await get_segment_count();
    }));

    let get_segment_count = async () => {
        let segment = document.querySelector('#segment').value;
        let website_id = document.querySelector('#website_id').value;

        if(segment == 'custom') {
            document.querySelector('#segment_count').innerHTML = ``;
            return;
        }

        /* Display a loader */
        document.querySelector('#segment_count').innerHTML = `<div class="spinner-border spinner-border-sm" role="status"></div>`;

        /* Prepare query string */
        let query = new URLSearchParams();

        /* Filter preparing on query string */
        if(segment == 'filter') {
            query = new URLSearchParams(new FormData(document.querySelector('#form')));
        }

        query.set('type', segment);
        query.set('website_id', website_id);

        /* Send request to server */
        let response = await fetch(`${url}/segments/get_segment_count?${query.toString()}`, {
            method: 'get',
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            /* :)  */
        }

        if(!response.ok) {
            /* :)  */
        }

        if(data.status == 'error') {
            /* :)  */
        } else if(data.status == 'success') {
            document.querySelector('#segment_count').innerHTML = `(${data.details.count})`;
        }
    }

    get_segment_count();

    /* Process selected website for segments */
    let process_segments = () => {
        /* Enable/disable segments based on the selected website id */
        let selected_website_id = document.querySelector('#website_id').value;

        document.querySelectorAll('#segment option[data-website-id]').forEach(element => {
            if(element.getAttribute('data-website-id') == selected_website_id) {
                element.removeAttribute('disabled');
            } else {
                element.setAttribute('disabled', 'disabled');
            }
        });
    };

    process_segments();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
