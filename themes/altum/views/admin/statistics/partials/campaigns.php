<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<div class="card mb-5">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-4">
            <h2 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-rocket fa-xs text-primary-900 mr-2"></i> <?= l('admin_campaigns.header') ?></h2>

            <div>
                <span class="badge <?= $data->total['campaigns'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= ($data->total['campaigns'] > 0 ? '+' : null) . nr($data->total['campaigns']) ?></span>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="campaigns"></canvas>
        </div>
    </div>
</div>

<?php $html = ob_get_clean() ?>

<?php ob_start() ?>
<script>
    'use strict';

    let color = css.getPropertyValue('--primary');
    let color_gradient = null;

    /* Display chart */
    let campaigns_chart = document.getElementById('campaigns').getContext('2d');
    color_gradient = campaigns_chart.createLinearGradient(0, 0, 0, 250);
    color_gradient.addColorStop(0, set_hex_opacity(color, 0.1));
    color_gradient.addColorStop(1, set_hex_opacity(color, 0.025));

    new Chart(campaigns_chart, {
        type: 'line',
        data: {
            labels: <?= $data->campaigns_chart['labels'] ?>,
            datasets: [
                {
                    label: <?= json_encode(l('admin_campaigns.title')) ?>,
                    data: <?= $data->campaigns_chart['campaigns'] ?? '[]' ?>,
                    backgroundColor: color_gradient,
                    borderColor: color,
                    fill: true
                }
            ]
        },
        options: chart_options
    });
</script>
<?php $javascript = ob_get_clean() ?>

<?php return (object) ['html' => $html, 'javascript' => $javascript] ?>
