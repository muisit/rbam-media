<?php
/**
 * wp-media-protector
 *
 * @package             wp-media-protector
 * @author              Michiel Uitdehaag
 * @copyright           2020 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         wp-media-protector
 * Plugin URI:          https://github.com/muisit/wp-media-protector
 * Description:         Protects WordPress media content using roles and users
 * Version:             1.0.0
 * Requires at least:   5.4
 * Requires PHP:        7.2
 * Author:              Michiel Uitdehaag
 * Author URI:          https://www.muisit.nl2br
 * License:             GNU GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:         wp-media-protector
 * Domain Path:         /languages
 *
 * This file is part of wp-media-protector.
 *
 * wp-media-protector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wp-media-protector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wp-media-protector.  If not, see <https://www.gnu.org/licenses/>.
 */


function wpmediaprotector_activate() {
    require_once(__DIR__.'/activate.php');
    $activator = new \WPMediaCreator\Activator();
    $activator->activate();
}

function wpmediaprotector_deactivate() {
    require_once(__DIR__.'/activate.php');
    $activator = new \WPMediaCreator\Activator();
    $activator->deactivate();
}

function wpmediaprotector_init() {
    if (filter_input(INPUT_GET, 'wp-media-protector')) {
        require_once(__DIR__.'/security.php');
        $actor = new \WPMediaProtector\Security();
    }
}

function wpmediaprotector_metabox($type, $post) {
    $editor = new \WPMediaProtector\Editor();
    $editor->metaBox($type, $post);
}

function wpmediaprotector_ajaxsearch() {
    require_once(__DIR__.'/editor.php');
    $editor = new \WPMediaProtector\Editor();
    $editor->ajaxSearch();
}

function wpmediaprotector_save($postid) {
    $editor = new \WPMediaProtector\Editor();
    $editor->save($postid);
}

function wpmediaprotector_loadpost() {
    require_once(__DIR__.'/editor.php');
    add_action('add_meta_boxes', 'wpmediaprotector_metabox', 9, 2 );   
    add_action('edit_attachment', 'wpmediaprotector_save', 10, 1 ); 
}

if (defined('ABSPATH')) {
    register_activation_hook( __FILE__, 'wpmediaprotector_activate' );
    register_deactivation_hook( __FILE__, 'wpmediaprotector_deactivate' );

    // action to protect an attachment
    add_action('init', 'wpmediaprotector_init' );

    // action to adjust the edit screen
    add_action('load-post.php', 'wpmediaprotector_loadpost');
    add_action('load-post-new.php', 'wpmediaprotector_loadpost');

    // action to provide AJAX responses
    add_action('wp_ajax_wpmediaprotector', 'wpmediaprotector_ajaxsearch');
}
