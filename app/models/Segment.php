<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Models;

class Segment extends Model {

    public function get_segments_by_user_id($user_id) {

        $data = [];

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('segments?user_id=' . $user_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            $result = database()->query("SELECT * FROM `segments` WHERE `user_id` = '{$user_id}'");

            while($row = $result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');
                $data[$row->segment_id] = $row;
            }

            cache()->save($cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id));

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

    public function get_segment_by_segment_id($segment_id) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('segment?segment_id=' . $segment_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $data = db()->where('segment_id', $segment_id)->getOne('segments');

            if($data) {
                $data->settings = json_decode($data->settings ?? '');

                /* Save to cache */
                cache()->save(
                    $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $data->user_id)
                );
            }

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

}
