<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Models;

class Campaign extends Model {

    public function get_campaign_by_campaign_id($campaign_id) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('campaign?campaign_id=' . $campaign_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $data = db()->where('campaign_id', $campaign_id)->getOne('campaigns');

            if($data) {
                $data->settings = json_decode($data->settings ?? '');

                /* Save to cache */
                cache()->save(
                    $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $data->user_id)->addTag('campaign_id=' . $data->campaign_id)
                );
            }

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

    public function delete($campaign_id) {

        $campaign = db()->where('campaign_id', $campaign_id)->getOne('campaigns', ['user_id', 'campaign_id', 'image']);

        if(!$campaign) return;

        /* Delete uploaded files */
        \Altum\Uploads::delete_uploaded_file($campaign->image, 'websites_campaigns_images');

        /* Delete the campaign */
        db()->where('campaign_id', $campaign_id)->delete('campaigns');

        /* Clear cache */
        cache()->deleteItemsByTag('campaign_id=' . $campaign_id);
        cache()->deleteItem('campaigns_total?user_id=' . $campaign->user_id);
        cache()->deleteItem('campaigns_dashboard?user_id=' . $campaign->user_id);

    }
}
