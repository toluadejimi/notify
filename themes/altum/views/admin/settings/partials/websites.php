<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group">
        <label for="service_worker_file_name"><?= l('admin_settings.websites.service_worker_file_name') ?></label>
        <div class="input-group">
            <input id="service_worker_file_name" name="service_worker_file_name" type="text" class="form-control" value="<?= settings()->websites->service_worker_file_name ?>" />
            <div class="input-group-append">
                <span class="input-group-text">.js</span>
            </div>
        </div>
        <small class="form-text text-muted"><?= l('admin_settings.websites.service_worker_file_name_help') ?></small>
    </div>

    <div class="form-group">
        <label for="pixel_exposed_identifier"><?= l('admin_settings.websites.pixel_exposed_identifier') ?></label>
        <input id="pixel_exposed_identifier" type="text" name="pixel_exposed_identifier" class="form-control" value="<?= settings()->websites->pixel_exposed_identifier ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.websites.pixel_exposed_identifier_help') ?></small>
    </div>

    <div class="form-group">
        <label for="pixel_cache"><?= l('admin_settings.websites.pixel_cache') ?></label>
        <div class="input-group">
            <input id="pixel_cache" type="number" min="0" name="pixel_cache" class="form-control" value="<?= settings()->websites->pixel_cache ?>" />
            <div class="input-group-append">
                <span class="input-group-text"><?= l('global.date.seconds') ?></span>
            </div>
        </div>
        <small class="form-text text-muted"><?= l('admin_settings.websites.pixel_cache_help') ?></small>
    </div>

    <div class="form-group">
        <label for="branding"><?= l('admin_settings.websites.branding') ?></label>
        <textarea id="branding" name="branding" class="form-control"><?= settings()->websites->branding ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.websites.branding_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="domains_is_enabled" name="domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->websites->domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="domains_is_enabled"><?= l('admin_settings.websites.domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.websites.domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="domains_custom_main_ip"><?= l('admin_settings.websites.domains_custom_main_ip') ?></label>
        <input id="domains_custom_main_ip" name="domains_custom_main_ip" type="text" class="form-control" value="<?= settings()->websites->domains_custom_main_ip ?>" placeholder="<?= $_SERVER['SERVER_ADDR'] ?>">
        <small class="form-text text-muted"><?= l('admin_settings.websites.domains_custom_main_ip_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_domains"><?= l('admin_settings.websites.blacklisted_domains') ?></label>
        <textarea id="blacklisted_domains" class="form-control" name="blacklisted_domains"><?= settings()->websites->blacklisted_domains ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.websites.blacklisted_domains_help') ?></small>
    </div>

    <?php foreach(['icon', 'campaign_image', 'flow_image'] as $key): ?>
        <div class="form-group">
            <label for="<?= $key . '_size_limit' ?>"><?= l('admin_settings.websites.' . $key . '_size_limit') ?></label>
            <div class="input-group">
                <input id="<?= $key . '_size_limit' ?>" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="<?= $key . '_size_limit' ?>" class="form-control" value="<?= settings()->websites->{$key . '_size_limit'} ?>" />
                <div class="input-group-append">
                    <span class="input-group-text"><?= l('global.mb') ?></span>
                </div>
            </div>
            <small class="form-text text-muted"><?= l('global.accessibility.admin_file_size_limit_help') ?></small>
        </div>
    <?php endforeach ?>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
