<?php
/**
 * Enables JS popups within Divi.
 *
 * @package     Popups_For_Divi
 * @author      Philipp Stracker
 * @copyright   2020 Philipp Stracker
 * @license     GPL-2.0-or-later
 *
 * Plugin Name: Popups for Divi
 * Plugin URI:  https://divimode.com/divi-popup/?utm_source=wpadmin&utm_medium=link&utm_campaign=popups-for-divi
 * Description: Finally, a simple and intuitive way to add custom popups to your Divi pages!
 * Author:      divimode.com
 * Author URI:  https://divimode.com/?utm_source=wpadmin&utm_medium=link&utm_campaign=popups-for-divi
 * Created:     30.12.2017
 * Version:     2.2.1
 * Text Domain: divi-popup
 * Domain Path: /lang
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Copyright (C) 2020 Philipp Stracker
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

defined( 'ABSPATH' ) || die();

/**
 * A new version value will force refresh of CSS and JS files for all users.
 */
define( 'DIVI_POPUP_VERSION', '2.2.1' );

define( 'DIVI_POPUP_PLUGIN_FILE', __FILE__ );
define( 'DIVI_POPUP_PLUGIN', plugin_basename( DIVI_POPUP_PLUGIN_FILE ) );
define( 'DIVI_POPUP_PATH', plugin_dir_path( DIVI_POPUP_PLUGIN_FILE ) );

/**
 * Simple handler for spl_autoload_register (autoload classes on demand).
 *
 * @since  0.1.0
 * @param  string $class Class name.
 * @return bool True if the class-file was found and loaded.
 */
function divi_popup_class_loader( $class ) {
	$class = strtolower( $class );

	if ( 'popups_for_divi' === substr( $class, 0, 15 ) ) {
		// Load a "Popups_For_Divi_" class from the includes folder.
		$module_file = str_replace( '_', '-', $class ) . '.php';
		$module_path = DIVI_POPUP_PATH . "include/class-{$module_file}";
	} else {
		// Not a Popups for Divi class, ignore it.
		return false;
	}

	if ( ! file_exists( $module_path ) ) {
		return false;
	}

	include_once $module_path;
	return true;
}

// Initialize the autoloader.
spl_autoload_register( 'divi_popup_class_loader' );

/**
 * Getter function that returns the main Divi_Areas_Pro instance.
 *
 * @since 2.0.4
 * @return Popups_For_Divi The main application instance.
 */
function divi_popup() {
	return $GLOBALS['divi_popup'];
}

// Hook up the plugin.
$GLOBALS['divi_popup'] = ( new Popups_For_Divi() )->setup_on( 'plugins_loaded' );
