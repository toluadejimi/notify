<?php defined('ALTUMCODE') || die() ?>

<?php foreach(['reset', 'broadcasts', 'campaigns', 'flows'] as $cron): ?>
    <div class="form-group">
        <label for="cron_<?= $cron ?>"><?= l('admin_settings.cron.' . $cron) ?></label>
        <div class="input-group">
            <input id="cron_<?= $cron ?>" name="cron_<?= $cron ?>" type="text" class="form-control" value="<?= '* * * * * wget --quiet -O /dev/null ' . SITE_URL . 'cron/' . $cron . '?key=' . settings()->cron->key ?>" readonly="readonly" />
            <div class="input-group-append">
                <button
                        type="button"
                        class="btn btn-light"
                        data-toggle="tooltip"
                        title="<?= l('global.clipboard_copy') ?>"
                        aria-label="<?= l('global.clipboard_copy') ?>"
                        data-copy="<?= l('global.clipboard_copy') ?>"
                        data-copied="<?= l('global.clipboard_copied') ?>"
                        data-clipboard-text="<?= '* * * * * wget --quiet -O /dev/null ' . SITE_URL . 'cron/' . $cron . '?key=' . settings()->cron->key ?>"
                >
                    <i class="fas fa-fw fa-sm fa-copy"></i>
                </button>
            </div>
            <div class="input-group-append">
                <a
                        href="<?= SITE_URL . 'cron/' . $cron . '?key=' . settings()->cron->key ?>"
                        target="_blank"
                        class="btn btn-light"
                        data-toggle="tooltip"
                        title="<?= l('admin_settings.cron.run_manually') ?>"
                >
                    <i class="fas fa-fw fa-sm fa-external-link-alt"></i>
                </a>
            </div>
        </div>
        <small class="form-text text-muted"><?= sprintf(l('admin_settings.cron.last_execution'), isset(settings()->cron->{$cron . '_datetime'}) ? \Altum\Date::get_timeago(settings()->cron->{$cron . '_datetime'}) : '-') ?></small>
    </div>
<?php endforeach ?>

<div <?= !\Altum\Plugin::is_active('push-notifications') ? 'data-toggle="tooltip" title="' . sprintf(l('admin_plugins.no_access'), \Altum\Plugin::get('push-notifications')->name ?? 'push-notifications') . '"' : null ?>>
    <div class="<?= !\Altum\Plugin::is_active('push-notifications') ? 'container-disabled' : null ?>">
        <div class="form-group">
            <label for="cron_push_notifications"><?= l('admin_settings.cron.push_notifications') ?></label>
            <div class="input-group">
                <input id="cron_push_notifications" name="cron_push_notifications" type="text" class="form-control" value="<?= '* * * * * wget --quiet -O /dev/null ' . SITE_URL . 'cron/push_notifications?key=' . settings()->cron->key ?>" readonly="readonly" />
                <div class="input-group-append">
                    <button
                            type="button"
                            class="btn btn-light"
                            data-toggle="tooltip"
                            title="<?= l('global.clipboard_copy') ?>"
                            aria-label="<?= l('global.clipboard_copy') ?>"
                            data-copy="<?= l('global.clipboard_copy') ?>"
                            data-copied="<?= l('global.clipboard_copied') ?>"
                            data-clipboard-text="<?= '* * * * * wget --quiet -O /dev/null ' . SITE_URL . 'cron/push_notifications?key=' . settings()->cron->key ?>"
                    >
                        <i class="fas fa-fw fa-sm fa-copy"></i>
                    </button>
                </div>
                <div class="input-group-append">
                    <a
                            href="<?= SITE_URL . 'cron/push_notifications?key=' . settings()->cron->key ?>"
                            target="_blank"
                            class="btn btn-light"
                            data-toggle="tooltip"
                            title="<?= l('admin_settings.cron.run_manually') ?>"
                    >
                        <i class="fas fa-fw fa-sm fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            <small class="form-text text-muted"><?= sprintf(l('admin_settings.cron.last_execution'), isset(settings()->cron->push_notifications_datetime) ? \Altum\Date::get_timeago(settings()->cron->push_notifications_datetime) : '-') ?></small>
        </div>
    </div>
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
