<?php defined('ALTUMCODE') || die() ?>

<div class="index-background py-7">
    <div class="container">
        <?= \Altum\Alerts::output_alerts() ?>

        <div class="row justify-content-center">
            <div class="col-11 col-md-10 col-lg-7">
                <div class="text-center mb-2">
                    <span class="badge badge-primary badge-pill"><i class="fas fa-fw fa-sm fa-check-circle mr-1"></i> <?= l('index.subheader2') ?></span>
                </div>

                <h1 class="index-header text-center mb-2"><?= l('index.header') ?></h1>
            </div>

            <div class="col-10 col-sm-8 col-lg-6">
                <p class="index-subheader text-center mb-5"><?= sprintf(l('index.subheader'), $data->total_sent_push_notifications) ?></p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-10 col-sm-8 col-lg-6">
                <div class="d-flex flex-column flex-lg-row justify-content-center">
                    <?php if(settings()->users->register_is_enabled): ?>
                        <a href="<?= url('register') ?>" class="btn btn-primary index-button mb-3 mb-lg-0">
                            <?= l('index.register') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                        </a>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center mt-7" data-aos="fade-up">
        <div class="col-12">
            <img src="<?= ASSETS_FULL_URL . 'images/index/hero.webp' ?>" class="img-fluid shadow-lg rounded-2x" loading="lazy" />
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="container">
    <div class="row">
        <div class="col-12 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-user-plus"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.steps.one') ?></h2>

                    <small class="text-muted m-0"><?= l('index.steps.one_text') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-user-plus"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.steps.two') ?></h2>

                    <small class="text-muted m-0"><?= l('index.steps.two_text') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="300">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-user-plus"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.steps.three') ?></h2>

                    <small class="text-muted m-0"><?= l('index.steps.three_text') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="container">
    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= ASSETS_FULL_URL . 'images/index/push_notifications.webp' ?>" class="img-fluid rounded-2x" loading="lazy" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.push_notifications.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.push_notifications.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.push_notifications.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.push_notifications.image') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.push_notifications.url') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.push_notifications.buttons') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.push_notifications.dynamic') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.push_notifications.others') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= ASSETS_FULL_URL . 'images/index/subscribers.webp' ?>" class="img-fluid rounded-2x" loading="lazy" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.subscribers.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.subscribers.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.subscribers.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.subscribers.location') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.subscribers.platforms') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.subscribers.referrer') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.subscribers.statistics') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.subscribers.logs') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= ASSETS_FULL_URL . 'images/index/campaigns.webp' ?>" class="img-fluid rounded-2x" loading="lazy" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.campaigns.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.campaigns.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.campaigns.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.campaigns.spintax') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.campaigns.custom_parameters') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.campaigns.segments') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.campaigns.statistics') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= ASSETS_FULL_URL . 'images/index/flows.webp' ?>" class="img-fluid rounded-2x" loading="lazy" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.flows.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.flows.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.flows.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.flows.one') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.flows.two') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.flows.three') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.flows.four') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= ASSETS_FULL_URL . 'images/index/segments.webp' ?>" class="img-fluid rounded-2x" loading="lazy" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.segments.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.segments.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.segments.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.custom') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.region') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.device') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.os') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.browsers') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.languages') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="p-4">
    <div class="py-6 rounded-2x bg-gray-100">
        <div class="container">
            <div class="text-center">
                <h2 class="h4"><?= l('index.browsers.header') ?> <i class="fas fa-fw fa-xs fa-circle-check text-success ml-1"></i> </h2>
                <p class="text-muted mb-0"><?= l('index.browsers.subheader') ?></p>
            </div>

            <div class="mt-5 d-flex justify-content-center align-items-center flex-wrap">
                <div class="p-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/os/apple.svg' ?>" class="index-os-icon" loading="lazy" data-toggle="tooltip" title="MacOs, iOS & iPadOS" alt="MacOs, iOS & iPadOS" />
                </div>

                <div class="p-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/os/android.svg' ?>" class="index-os-icon" loading="lazy" data-toggle="tooltip" title="Android" alt="Android" />
                </div>

                <div class="p-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/os/windows.svg' ?>" class="index-os-icon" loading="lazy" data-toggle="tooltip" title="Windows" alt="Windows" />
                </div>

                <div class="p-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/os/ubuntu.svg' ?>" class="index-os-icon" loading="lazy" data-toggle="tooltip" title="Ubuntu" alt="Ubuntu" />
                </div>

                <div class="p-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/os/chromeos.svg' ?>" class="index-os-icon" loading="lazy" data-toggle="tooltip" title="ChromeOS" alt="ChromeOS" />
                </div>

                <div class="p-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/os/linux.svg' ?>" class="index-os-icon" loading="lazy" data-toggle="tooltip" title="Linux" alt="Linux" />
                </div>
            </div>

            <div class="mt-3 row justify-content-around">
                <div class="col-6 col-md-4 col-lg-3 p-3">
                    <div class="card h-100 zoom-animation-subtle border-0">
                        <div class="card-body d-flex flex-column align-items-center">
                            <img src="<?= ASSETS_FULL_URL . 'images/browsers/chrome.svg' ?>" class="index-browser-icon" loading="lazy" alt="Chrome" />

                            <div class="h6 mt-3 text-center">Chrome</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3 p-3">
                    <div class="card h-100 zoom-animation-subtle border-0">
                        <div class="card-body d-flex flex-column align-items-center">
                            <img src="<?= ASSETS_FULL_URL . 'images/browsers/safari.svg' ?>" class="index-browser-icon" loading="lazy" alt="Safari" />

                            <div class="h6 mt-3 text-center">Safari</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3 p-3">
                    <div class="card h-100 zoom-animation-subtle border-0">
                        <div class="card-body d-flex flex-column align-items-center">
                            <img src="<?= ASSETS_FULL_URL . 'images/browsers/edge.svg' ?>" class="index-browser-icon" loading="lazy" alt="Edge" />

                            <div class="h6 mt-3 text-center">Edge</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3 p-3">
                    <div class="card h-100 zoom-animation-subtle border-0">
                        <div class="card-body d-flex flex-column align-items-center">
                            <img src="<?= ASSETS_FULL_URL . 'images/browsers/firefox.svg' ?>" class="index-browser-icon" loading="lazy" alt="Firefox" />

                            <div class="h6 mt-3 text-center">Firefox</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3 p-3">
                    <div class="card h-100 zoom-animation-subtle border-0">
                        <div class="card-body d-flex flex-column align-items-center">
                            <img src="<?= ASSETS_FULL_URL . 'images/browsers/samsung.svg' ?>" class="index-browser-icon" loading="lazy" alt="Samsung Internet" />

                            <div class="h6 mt-3 text-center">Samsung Internet</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3 p-3">
                    <div class="card h-100 zoom-animation-subtle border-0">
                        <div class="card-body d-flex flex-column align-items-center">
                            <img src="<?= ASSETS_FULL_URL . 'images/browsers/opera.svg' ?>" class="index-browser-icon" loading="lazy" alt="Opera" />

                            <div class="h6 mt-3 text-center">Opera</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="<?= url('help/platforms-browsers-support') ?>" class="small text-muted text-decoration-none">
                    <?= l('global.view_all') ?> <i class="fas fa-fw fa-sm fa-chevron-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="container">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-4 p-4">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="100">
                <img src="<?= ASSETS_FULL_URL . 'images/index/widget.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('index.widget.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.widget.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="200">
                <img src="<?= ASSETS_FULL_URL . 'images/index/button.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('index.button.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.button.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="300">
                <img src="<?= ASSETS_FULL_URL . 'images/index/customizability.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('index.customizability.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.customizability.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="400">
                <img src="<?= ASSETS_FULL_URL . 'images/index/export.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('index.export.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.export.subheader') ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 p-4">
            <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="500">
                <img src="<?= ASSETS_FULL_URL . 'images/index/custom_parameters.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" />

                <div>
                    <div class="mb-2">
                        <span class="h5"><?= l('index.custom_parameters.header') ?></span>
                    </div>
                    <span class="text-muted"><?= l('index.custom_parameters.subheader') ?></span>
                </div>
            </div>
        </div>

        <?php if(settings()->websites->domains_is_enabled): ?>
            <div class="col-12 col-md-6 col-lg-4 p-4">
                <div class="d-flex flex-column justify-content-between h-100" data-aos="fade-up" data-aos-delay="600">
                    <img src="<?= ASSETS_FULL_URL . 'images/index/domains.webp' ?>" class="img-fluid rounded-2x mb-4" loading="lazy" />

                    <div>
                        <div class="mb-2">
                            <span class="h5"><?= l('index.domains.header') ?></span>
                        </div>
                        <span class="text-muted"><?= l('index.domains.subheader') ?></span>
                    </div>
                </div>
            </div>
        <?php endif ?>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="p-4">
    <div class="card rounded-2x bg-gray-900">
        <div class="card-body py-5 py-lg-6 text-center">
            <span class="h3 text-gray-100"><?= sprintf(l('index.stats'), nr($data->total_websites, 0, true, true), nr($data->total_subscribers, 0, true, true)) ?></span>
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="container">
    <div class="text-center mb-4">
        <h2><?= l('index.notifications_handlers.header') ?> <i class="fas fa-fw fa-xs fa-bell ml-1"></i> </h2>
        <p class="text-muted"><?= l('index.notifications_handlers.subheader') ?></p>
    </div>

    <div class="row">
        <?php $notification_handlers = require APP_PATH . 'includes/notification_handlers.php' ?>
        <?php foreach($notification_handlers as $key => $notification_handler): ?>
            <div class="col-6 col-lg-4 p-4" data-aos="fade-up">
                <div class="position-relative w-100 h-100 icon-zoom-animation">
                    <div class="position-absolute rounded-2x w-100 h-100" style="background: <?= $notification_handler['color'] ?>;opacity: 0.05;"></div>

                    <div class="rounded-2x w-100 p-4 text-truncate text-center">
                        <div><i class="<?= $notification_handler['icon'] ?> fa-fw fa-xl mx-1" style="color: <?= $notification_handler['color'] ?>"></i></div>

                        <div class="mt-3 mb-0 h6"><?= l('notification_handlers.input.type_' . $key) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>

<?php if(settings()->main->display_index_testimonials): ?>
    <div class="my-5">&nbsp;</div>

    <div class="p-4">
        <div class="mt-5 py-7 bg-primary-100 rounded-2x">
            <div class="container">
                <div class="text-center">
                    <h2><?= l('index.testimonials.header') ?> <i class="fas fa-fw fa-xs fa-check-circle text-primary"></i></h2>
                </div>

                <div class="row mt-8">
                    <?php foreach(['one', 'two', 'three'] as $key => $value): ?>
                        <div class="col-12 col-lg-4 mb-6 mb-lg-0" data-aos="fade-up" data-aos-delay="<?= $key * 100 ?>">
                            <div class="card border-0 zoom-animation">
                                <div class="card-body">
                                    <img src="<?= ASSETS_FULL_URL . 'images/index/testimonial-' . $value . '.jpeg' ?>" class="img-fluid index-testimonial-avatar" alt="<?= l('index.testimonials.' . $value . '.name') . ', ' . l('index.testimonials.' . $value . '.attribute') ?>" loading="lazy" />

                                    <p class="mt-5">
                                        <span class="text-gray-800 font-weight-bold text-muted h5">“</span>
                                        <span><?= l('index.testimonials.' . $value . '.text') ?></span>
                                        <span class="text-gray-800 font-weight-bold text-muted h5">”</span>
                                    </p>

                                    <div class="blockquote-footer mt-4">
                                        <span class="font-weight-bold"><?= l('index.testimonials.' . $value . '.name') ?></span>, <span class="text-muted"><?= l('index.testimonials.' . $value . '.attribute') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(settings()->main->display_index_plans): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= l('index.pricing.header') ?></h2>
            <p class="text-muted"><?= l('index.pricing.subheader') ?></p>
        </div>

        <?= $this->views['plans'] ?>
    </div>
<?php endif ?>

<?php if(settings()->main->display_index_faq): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= sprintf(l('index.faq.header'), '<span class="text-primary">', '</span>') ?></h2>
        </div>

        <div class="accordion index-faq" id="faq_accordion">
            <?php foreach(['one', 'two', 'three', 'four'] as $key): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="" id="<?= 'faq_accordion_' . $key ?>">
                            <h3 class="mb-0">
                                <button class="btn btn-lg font-weight-bold btn-block d-flex justify-content-between text-gray-800 px-0 icon-zoom-animation" type="button" data-toggle="collapse" data-target="<?= '#faq_accordion_answer_' . $key ?>" aria-expanded="true" aria-controls="<?= 'faq_accordion_answer_' . $key ?>">
                                    <span><?= l('index.faq.' . $key . '.question') ?></span>

                                    <span data-icon>
                                        <i class="fas fa-fw fa-circle-chevron-down"></i>
                                    </span>
                                </button>
                            </h3>
                        </div>

                        <div id="<?= 'faq_accordion_answer_' . $key ?>" class="collapse text-muted mt-2" aria-labelledby="<?= 'faq_accordion_' . $key ?>" data-parent="#faq_accordion">
                            <?= l('index.faq.' . $key . '.answer') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <?php ob_start() ?>
    <script>
        'use strict';

        $('#faq_accordion').on('show.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.transform = 'rotate(180deg)';
            svg.style.color = 'var(--primary)';
        })

        $('#faq_accordion').on('hide.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.color = 'var(--primary-800)';
            svg.style.removeProperty('transform');
        })
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
<?php endif ?>

<?php if(settings()->users->register_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="card border-0 index-cta py-5 py-lg-6" data-aos="fade-up">
            <div class="card-body">
                <div class="row align-items-center justify-content-center">
                    <div class="col-12 col-lg-5">
                        <div class="text-center text-lg-left mb-4 mb-lg-0">
                            <h2 class="h1"><?= l('index.cta.header') ?></h2>
                            <p class="h5"><?= l('index.cta.subheader') ?></p>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                        <div class="text-center text-lg-right">
                            <?php if(\Altum\Authentication::check()): ?>
                                <a href="<?= url('dashboard') ?>" class="btn btn-outline-primary zoom-animation">
                                    <?= l('dashboard.menu') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?= url('register') ?>" class="btn btn-outline-primary zoom-animation">
                                    <?= l('index.cta.register') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                </a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>


<?php if(count($data->blog_posts)): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= sprintf(l('index.blog.header'), '<span class="text-primary">', '</span>') ?></h2>
        </div>

        <div class="row">
            <?php foreach($data->blog_posts as $blog_post): ?>
                <div class="col-12 col-lg-4 p-4">
                    <div class="card h-100 zoom-animation-subtle">
                        <div class="card-body">
                            <?php if($blog_post->image): ?>
                                <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>" aria-label="<?= $blog_post->title ?>">
                                    <img src="<?= \Altum\Uploads::get_full_url('blog') . $blog_post->image ?>" class="blog-post-image-small img-fluid w-100 rounded mb-4" loading="lazy" />
                                </a>
                            <?php endif ?>

                            <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>">
                                <h3 class="h5 card-title mb-2"><?= $blog_post->title ?></h3>
                            </a>

                            <p class="text-muted mb-0"><?= $blog_post->description ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
<?php endif ?>


<?php ob_start() ?>
<link rel="stylesheet" href="<?= ASSETS_FULL_URL . 'css/libraries/aos.min.css?v=' . PRODUCT_CODE ?>">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/aos.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    AOS.init({
        delay: 100,
        duration: 600
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
