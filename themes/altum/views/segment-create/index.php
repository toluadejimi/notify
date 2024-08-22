<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('segments') ?>"><?= l('segments.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('segment_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-layer-group mr-1"></i> <?= l('segment_create.header') ?></h1>
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
                    <label for="type"><i class="fas fa-fw fa-sm fa-layer-group text-muted mr-1"></i> <?= l('global.type') ?> <span id="segment_count"></span></label>
                    <select id="type" name="type" class="form-control <?= \Altum\Alerts::has_field_errors('type') ? 'is-invalid' : null ?>" required="required">
                        <option value="custom" <?= $data->values['type'] == 'custom' ? 'selected="selected"' : null ?>><?= l('segments.type.custom') ?></option>
                        <option value="filter" <?= $data->values['type'] == 'filter' ? 'selected="selected"' : null ?>><?= l('segments.type.filter') ?></option>
                    </select>
                    <?= \Altum\Alerts::output_field_error('segment') ?>
                </div>

                <div class="form-group" data-type="custom">
                    <label for="subscribers_ids"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('segments.subscribers_ids') ?></label>
                    <input type="text" id="subscribers_ids" name="subscribers_ids" value="<?= $data->values['subscribers_ids'] ?>" class="form-control <?= \Altum\Alerts::has_field_errors('subscribers_ids') ? 'is-invalid' : null ?>" placeholder="<?= l('segments.subscribers_ids_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('subscribers_ids') ?>
                    <small class="form-text text-muted"><?= l('segments.subscribers_ids_help') ?></small>
                </div>

                <div class="form-group" data-type="filter">
                    <div class="form-group">
                        <label for="filters_continents"><i class="fas fa-fw fa-sm fa-globe-europe text-muted mr-1"></i> <?= l('global.continents') ?></label>
                        <select id="filters_continents" name="filters_continents[]" class="custom-select" multiple="multiple">
                            <?php foreach(get_continents_array() as $continent_code => $continent_name): ?>
                                <option value="<?= $continent_code ?>" <?= in_array($continent_code,$data->values['filters_continents'] ?? []) ? 'selected="selected"' : null ?>><?= $continent_name ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-type="filter">
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

                <div class="form-group" data-type="filter">
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

                <div class="form-group" data-type="filter">
                    <label for="filters_operating_systems"><i class="fas fa-fw fa-server fa-sm text-muted mr-1"></i> <?= l('segments.operating_systems') ?></label>
                    <select id="filters_operating_systems" name="filters_operating_systems[]" class="custom-select" multiple="multiple">
                        <?php foreach(['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS'] as $os_name): ?>
                            <option value="<?= $os_name ?>" <?= in_array($os_name, $data->values['filters_operating_systems'] ?? []) ? 'selected="selected"' : null ?>><?= $os_name ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group" data-type="filter">
                    <label for="filters_browsers"><i class="fas fa-fw fa-window-restore fa-sm text-muted mr-1"></i> <?= l('segments.browsers') ?></label>
                    <select id="filters_browsers" name="filters_browsers[]" class="custom-select" multiple="multiple">
                        <?php foreach(['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet'] as $browser_name): ?>
                            <option value="<?= $browser_name ?>" <?= in_array($browser_name, $data->values['filters_browsers'] ?? []) ? 'selected="selected"' : null ?>><?= $browser_name ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group" data-type="filter">
                    <label for="filters_languages"><i class="fas fa-fw fa-language fa-sm text-muted mr-1"></i> <?= l('segments.languages') ?></label>
                    <select id="filters_languages" name="filters_languages[]" class="custom-select" multiple="multiple">
                        <?php foreach(get_locale_languages_array() as $locale => $language): ?>
                            <option value="<?= $locale ?>" <?= in_array($locale, $data->values['filters_languages'] ?? []) ? 'selected="selected"' : null ?>><?= $language ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    type_handler('[name="type"]', 'data-type');
    document.querySelector('[name="type"]') && document.querySelectorAll('[name="type"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="type"]', 'data-type'); }));

    document.querySelector('#website_id').addEventListener('change', async event => {
        await get_segment_count();
    });

    document.querySelector('#type').addEventListener('change', async event => {
        await get_segment_count();
    });

    document.querySelectorAll('[name^="filters_"]').forEach(element => element.addEventListener('change', async event => {
        await get_segment_count();
    }));

    let get_segment_count = async () => {
        let type = document.querySelector('#type').value;
        let website_id = document.querySelector('#website_id').value;

        if(type == 'custom') {
            document.querySelector('#segment_count').innerHTML = ``;
            return;
        }

        /* Display a loader */
        document.querySelector('#segment_count').innerHTML = `<div class="spinner-border spinner-border-sm" role="status"></div>`;

        /* Prepare query string */
        let query = new URLSearchParams();
        query.set('type', type);
        query.set('website_id', website_id);

        /* Filter preparing on query string */
        if(type == 'filter') {
            query = new URLSearchParams(new FormData(document.querySelector('#form')));
        }

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
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
