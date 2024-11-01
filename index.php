<?php

/**
 * Plugin Name: 	  Video Player by Widgetic
 * Contributors: 	  Widgetic
 * Plugin URI:		  https://wordpress.org/plugins/video-player-by-widgetic/
 * Description: 	  Automatically generate a playlist from your songs.
 * Version: 		  1.0.3
 * Requires at least: 5.4
 * Requires PHP:      7.0
 * Author: 			  Widgetic
 * Author URI: 		  https://profiles.wordpress.org/widgetic/
 * License: 		  GPL v2
 */


defined("ABSPATH") || exit;

// IMPORT DASHBOARD
require_once(plugin_dir_path(__FILE__) . "dashboard.php");

// IMPORT BLOCK
require_once(plugin_dir_path(__FILE__) . "block.php");