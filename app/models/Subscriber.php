<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Models;

class Subscriber extends Model {

    public function get_subscriber_by_subscriber_id($subscriber_id) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('subscriber?subscriber_id=' . $subscriber_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $data = db()->where('subscriber_id', $subscriber_id)->getOne('subscribers');

            if($data) {
                /* Save to cache */
                cache()->save(
                    $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $data->user_id)->addTag('subscriber_id=' . $data->subscriber_id)
                );
            }

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

}
