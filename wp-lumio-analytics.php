<?php
/**
 * Plugin Name: Lumio analytics
 * Plugin URI: https://modulo.lumio-analytics.com/worpress
 * Description: Add Tracking script for Lumio analytics and Informative panel Lumio analytic in detail.
 * Version: 1.0.0
 * Author: Lumio
 * Author URI: http://lumio-analytics.com/
 *
 * @package lumioanalytics/wp-lumio-analytics
 */

define('WLA_DIR', __DIR__);
define('WLA_NAME', 'Lumio analytics WordPress Plugin');
define('WLA_VERSION', '1.0.0');

spl_autoload_register(
    function ( $class ) {
        $base_dir = __DIR__ . '/lib/'; // your classes folder.
        $file     = $base_dir . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file) ) {
            include $file;
        }
    }
);
require_once __DIR__ . '/vendor/autoload.php';

register_activation_hook(
    __FILE__,
    array( '\Lumio\wp_lumio_analytics\Plugin', 'action_activate' )
);

register_deactivation_hook(
    __FILE__,
    array( '\Lumio\wp_lumio_analytics\Plugin', 'action_deactivate' )
);

\Lumio\wp_lumio_analytics\Plugin::init();
