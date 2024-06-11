<?php
/**
 * Plugin name: Contact Management System
 * Description: This is a test plugin for Contact Management
 * Author: Bipul Karmokar
 * Author URI: https://www.bipul.com
 * Version: 1.0
 * Requires PHP: 7.4
 */

define("CMS_PLUGIN_PATH",plugin_dir_path(__FILE__));
define("CMS_PLUGIN_URL",plugin_dir_url(__FILE__));

include_once CMS_PLUGIN_PATH.'class/ContactManagement.php';

$contactManagementObj = new ContactManagement();

register_activation_hook( __FILE__, array($contactManagementObj,"createContactTable"));
register_deactivation_hook( __FILE__, array($contactManagementObj,"dropTable"));