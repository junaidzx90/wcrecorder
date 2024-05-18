<?php

/**
 * Fired during plugin activation
 *
 * @link       https://fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Wcrecorder
 * @subpackage Wcrecorder/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wcrecorder
 * @subpackage Wcrecorder/includes
 * @author     Devjoo <contact@easeare.com>
 */
class Wcrecorder_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		flush_rewrite_rules();
	}

}
