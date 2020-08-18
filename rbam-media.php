<?php
/**
 * rbam-media
 *
 * @package             rbam-media
 * @author              Michiel Uitdehaag
 * @copyright           2020 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         rbam-media
 * Plugin URI:          https://github.com/muisit/rbam-media
 * Description:         Protects WordPress media content using roles based access
 * Version:             1.1.0
 * Requires at least:   5.4
 * Requires PHP:        7.2
 * Author:              Michiel Uitdehaag
 * Author URI:          https://www.muisit.nl
 * License:             GNU GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:         rbam-media
 * Domain Path:         /languages
 *
 * This file is part of rbam-media.
 *
 * rbam-media is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * rbam-media is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with rbam-media.  If not, see <https://www.gnu.org/licenses/>.
 */


function rbammedia_activate() {
    require_once(__DIR__.'/activate.php');
    $activator = new \RBAM\Activator();
    $activator->activate();
}

function rbammedia_deactivate() {
    require_once(__DIR__.'/activate.php');
    $activator = new \RBAM\Activator();
    $activator->deactivate();
}

function rbammedia_init() {
    if (filter_input(INPUT_GET, 'rbam-media')) {
        require_once(__DIR__.'/security.php');
        $actor = new \RBAM\Security();
    }
}

function rbammedia_metabox($type, $post) {
    $editor = new \RBAM\Editor();
    $editor->metaBox($type, $post);
}

function rbammedia_ajaxsearch() {
    require_once(__DIR__.'/editor.php');
    $editor = new \RBAM\Editor();
    $editor->ajaxSearch();
}

function rbammedia_save($postid) {
    $editor = new \RBAM\Editor();
    $editor->save($postid);
}

function rbammedia_loadpost() {
    require_once(__DIR__.'/editor.php');
    add_action('add_meta_boxes', 'rbammedia_metabox', 9, 2 );   
    add_action('edit_attachment', 'rbammedia_save', 10, 1 ); 
}

if (defined('ABSPATH')) {
    register_activation_hook( __FILE__, 'rbammedia_activate' );
    register_deactivation_hook( __FILE__, 'rbammedia_deactivate' );

    // action to protect an attachment
    add_action('init', 'rbammedia_init' );

    // action to adjust the edit screen
    add_action('load-post.php', 'rbammedia_loadpost');
    add_action('load-post-new.php', 'rbammedia_loadpost');

    // action to provide AJAX responses
    add_action('wp_ajax_rbammedia', 'rbammedia_ajaxsearch');
}
