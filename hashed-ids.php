<?php
/**
 * @package GoldFash Hashed IDs
 * @version 1.0
 */
/*
Plugin Name: Hashed-ids
Plugin URL: https://goldfash.com:443/plugins
Description: Link Hashing and Encrypting
Version: 1.0
Author: GoldFash Design
Author URI:        https://goldfash.com:443/
Contributors:      raceanf
Domain Path:       /languages
Text Domain:       hashed-ids
GitHub Plugin URI: https://github.com/goldfashhosting/hashed-ids
GitHub Branch:     master
*/

require_once('hashids/lib/hashids.php-5-3.php');

define ('HASHED_IDS_MIN_LENGTH', 37);

function hashed_id() {
    global $wp_rewrite;
    add_rewrite_tag('%hashed_id%','([^/]+)');
    $permalink = $wp_rewrite->permalink_structure;
    if (!empty($permalink) && false !== strpos( $permalink, '%hashed_id%' )) {
        add_filter('pre_post_link', '_hashed_id_post_link', 37, 2);
        add_filter('parse_request', '_hashed_id_parse_request');
    }
}

function _hashed_id_post_link($permalink, $post) {
    $hashids = new hashids(AUTH_KEY, HASHED_IDS_MIN_LENGTH);
    $permalink = str_replace('%hashed_id%', $hashids->encrypt((int)$post->ID), $permalink);
    return $permalink;
}

function _hashed_id_parse_request($qv) {
    $hashed_id = $qv->query_vars['hashed_id'];
    if (strlen($hashed_id) > 0) {
        $hashids = new hashids(AUTH_KEY, HASHED_IDS_MIN_LENGTH);
        $id = $hashids->decrypt($hashed_id);
        if (isset($id[0]) && is_numeric($id[0])) {
            $qv->query_vars['p'] = $id[0];
        } else {
            $qv->query_vars['pagename'] = $hashed_id;
        }
    }
    return $qv;
}
add_action('init', 'hashed_id');

function hashed_ids_activate_plugin() {
    global $wp_rewrite;
    if ($wp_rewrite->using_permalinks()) {
        $wp_rewrite->set_permalink_structure(
                str_replace('%post_id%', '%hashed_id%', $wp_rewrite->permalink_structure)
        );
    }
    flush_rewrite_rules(false);
}
register_activation_hook( __FILE__, 'hashed_ids_activate_plugin' );

function hashed_ids_deactivate_plugin() {
    global $wp_rewrite;
    if ($wp_rewrite->using_permalinks()) {
        $wp_rewrite->set_permalink_structure(
                str_replace('%hashed_id%', '%post_id%', $wp_rewrite->permalink_structure)
        );
    }
    flush_rewrite_rules(false);
}
register_deactivation_hook( __FILE__, 'hashed_ids_deactivate_plugin' );


?>
