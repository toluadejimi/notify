<?php defined('ALTUMCODE') || die() ?>
<script>
    let url = <?= json_encode(SITE_URL) ?>;
    let title = <?= json_encode(settings()->main->title) ?>;

    self.addEventListener('push', (event) => {
        if(!(self.Notification && self.Notification.permission === 'granted')) {
            return;
        }

        if(event.data) {
            const notification = event.data.json();

            let options = {
                body: notification.description ?? null,
                icon: notification.icon ?? null,
                image: notification.image ?? null,
                silent: notification.is_silent ?? null,
                requireInteraction: !(notification.is_auto_hide ?? null),
                data: {
                    notification_url: notification.url ?? null,
                    button_url_1: notification.button_url_1 ?? null,
                    button_url_2: notification.button_url_2 ?? null,
                    campaign_id: notification.campaign_id ?? null,
                    flow_id: notification.flow_id ?? null,
                    subscriber_id: notification.subscriber_id,
                }
            };

            let actions = [];

            /* Button one */
            if(notification.button_title_1 && notification.button_url_1) {
                actions.push({
                    action: 'button_click_1',
                    title: notification.button_title_1,
                })
            }

            /* Button two */
            if(notification.button_title_2 && notification.button_url_2) {
                actions.push({
                    action: 'button_click_2',
                    title: notification.button_title_2,
                })
            }

            /* Add the actions / buttons */
            options['actions'] = actions;

            /* Display the notification */
            event.waitUntil(self.registration.showNotification(notification.title, options));

            let notification_type = notification.campaign_id ? 'campaign_id' : 'flow_id';
            let notification_type_value = notification.campaign_id ?? notification.flow_id;

            /* Send statistics logs */
            event.waitUntil(send_tracking_data({
                type: 'displayed_notification',
                subscriber_id: notification.subscriber_id,
                [notification_type]: notification_type_value,
            }));
        }
    });

    self.addEventListener('notificationclick', (event) => {
        event.notification.close();

        let url = null;

        if(event.action.startsWith('button_click')) {

            if(event.action == 'button_click_1') url = event.notification.data.button_url_1;
            if(event.action == 'button_click_2') url = event.notification.data.button_url_2;

        } else {
            if(event.notification.data.notification_url) {
                url = event.notification.data.notification_url;
            }
        }

        /* Open URL if needed */
        if(url) {
            let notification_type = event.notification.data.campaign_id ? 'campaign_id' : 'flow_id';
            let notification_type_value = event.notification.data.campaign_id ?? event.notification.data.flow_id;

            /* Send statistics logs */
            event.waitUntil(send_tracking_data({
                type: 'clicked_notification',
                subscriber_id: event.notification.data.subscriber_id,
                [notification_type]: notification_type_value,
            }));

            event.waitUntil(clients.openWindow(url));
        }

    });

    self.addEventListener('notificationclose', (event) => {
        let notification_type = event.notification.data.campaign_id ? 'campaign_id' : 'flow_id';
        let notification_type_value = event.notification.data.campaign_id ?? event.notification.data.flow_id;

        /* Send statistics logs */
        event.waitUntil(send_tracking_data({
            type: 'closed_notification',
            subscriber_id: event.notification.data.subscriber_id,
            [notification_type]: notification_type_value,
        }));
    });

    /* Helper to easily send logs */
    let send_tracking_data = async data => {
        try {
            let response = await fetch(`${url}pixel-track/${website_pixel_key}`, {
                method: 'post',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
        } catch (error) {
            console.log(`${title} (${url}): ${error}`);
        }
    }
</script>
