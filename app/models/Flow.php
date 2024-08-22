<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Models;

class Flow extends Model {

    public function get_flow_by_flow_id($flow_id) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('flow?flow_id=' . $flow_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $data = db()->where('flow_id', $flow_id)->getOne('flows');

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

    public function get_flows_by_user_id($user_id) {

        $data = [];

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('flows?user_id=' . $user_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            $result = database()->query("SELECT * FROM `flows` WHERE `user_id` = '{$user_id}'");

            while($row = $result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');

                $data[$row->flow_id] = $row;
            }

            cache()->save($cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id));

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

    public function get_flows_by_website_id($website_id) {

        $data = [];

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('flows?website_id=' . $website_id);
        $user_id = null;

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            $result = database()->query("SELECT * FROM `flows` WHERE `website_id` = '{$website_id}'");

            while($row = $result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');

                $data[$row->flow_id] = $row;

                $user_id = $row->user_id;
            }

            cache()->save($cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id));

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

    public function delete($flow_id) {

        $flow = db()->where('flow_id', $flow_id)->getOne('flows', ['user_id', 'flow_id', 'settings', 'image']);

        if(!$flow) return;

        $flow->settings = json_decode($flow->settings ?? '');

        /* Delete uploaded files */
        \Altum\Uploads::delete_uploaded_file($flow->image, 'websites_flows_images');

        /* Delete the flow */
        db()->where('flow_id', $flow_id)->delete('flows');

        /* Clear cache */
        cache()->deleteItem('flow?flow_id=' . $flow->flow_id);
        cache()->deleteItem('flows?user_id=' . $flow->user_id);
        cache()->deleteItem('flows?website_id=' . $flow->website_id);

    }
}
