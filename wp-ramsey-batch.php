<?php
/*
Plugin Name:  Ramsey Batch
Plugin URI:   https://www.daveramsey.com
Description:  Provides a framework and UI for running batch jobs inside of WordPress.
Version:      1.2.0
Author:       Philip Downer<philip.downer@daveramsey.com>, Alex MacArthur<alex.macarthur@daveramsey.com>
License:      GPLv3
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
*/

//Disallow direct file access
if (!defined('ABSPATH')) {
    die('Direct file access is not allowed.');
}

//Autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

use RamseySolutions\RamseyBatch\Views\BatchView;
use RamseySolutions\RamseyBatch\Controllers\BatchController;

define('RB_PLUGIN_SLUG', 'ramsey-batch');
define('RB_PLUGIN_ROOT', dirname(__FILE__));
define('RB_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));

add_action('admin_menu', function () {
    add_submenu_page('tools.php', 'Batch Jobs', 'Batch Jobs', 'manage_options', RB_PLUGIN_SLUG, 'ramseyBatchDisplayAdminPage');
});

function ramseyBatchDisplayAdminPage()
{
    $page = new BatchView(new BatchController, RB_PLUGIN_SLUG, 'Batch Jobs');
    $page->display();
}

add_action('admin_init', function () {
    add_action('wp_ajax_' . RB_PLUGIN_SLUG, 'RamseySolutions\RamseyBatch\Controllers\BatchController::runJob');
    add_action('wp_ajax_' . RB_PLUGIN_SLUG . '-item', 'RamseySolutions\RamseyBatch\Controllers\BatchController::runJobItem');
});

add_action('admin_enqueue_scripts', function () {
    BatchController::enqueueScripts();
});
