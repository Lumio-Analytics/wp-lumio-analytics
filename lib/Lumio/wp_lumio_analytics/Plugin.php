<?php

namespace Lumio\wp_lumio_analytics;

class Plugin
{
    private $_key = null;
    private $_is_valid_key = false;

    /**
     * Hook onto all of the actions and filters needed by the plugin.
     */
    protected function __construct()
    {
        $this->_key          = get_option('wla_key', '');
        $this->_is_valid_key = self::is_valid_key($this->_key);
        if (is_admin()) {
            $plugin_file = plugin_basename(__FILE__);
            add_action('init', array($this, 'action_init'));
            add_filter("plugin_action_links_{$plugin_file}", array($this, 'plugin_action_links'), 10, 4);
            add_action('admin_menu', array($this, 'action_admin_menu'));
            add_action('admin_notices', array($this, 'invalid_key_admin_menu'));
        } else {
            add_action('wp_footer', array($this, 'lumio_analytics_tracking_code'));
        }
    }

    public function lumio_analytics_tracking_code()
    {
        if (self::is_valid_key($this->_key)) {
            ?>
            <script
                type="text/javascript"
                async key="<?php echo $this->_key; ?>"
                src="https://app.lumio-analytics.com/widgets/lumio-analytics.js">
            </script>
        <?php
        }
    }

    private static function is_valid_key($key)
    {
        return preg_match('/^\w{40}$/', $key);
    }

    /**
     * Run once on plugin activation
     */
    public static function action_activate()
    {
        //Should we read any configuration from 3rd party?

        //Set version as an option get_plugin_data function is not availiable at cron
        do_action('wla_activation');
    }

    /**
     * Run once on plugin activation
     */
    public static function action_deactivate()
    {
        self::init()->registerLumioIntegration(false);
        update_option('wla_key', '');
    }

    public static function init()
    {
        static $instance = null;

        if (! $instance) {
            $instance = new Plugin;
        }

        return $instance;
    }

    /**
     * Run using the 'init' action.
     */
    public function action_init()
    {
        load_plugin_textdomain('wla', false, dirname(plugin_basename(__FILE__)) . '/gettext');
        $this->can_save_key();
    }

    /**
     * Adds options & management pages to the admin menu.
     *
     * Run using the 'admin_menu' action.
     */
    public function action_admin_menu()
    {
        $admin_icon = self::get_admin_icon_b64(Plugin::is_lumio_page() ? '#fff' : false);

        add_menu_page(
            __('Lumio', 'wla'),
            __('Lumio', 'wla'),
            'manage_options',
            'wla_settings',
            array($this, 'settings_page'),
            $admin_icon,
            '10'
        );
    }

    // Returns true if the current page is one of Lumio Analytics pages. Returns false if not

    /**
     * Gets the admin icon for the Lumio menu item
     *
     * @since  Unknown
     * @access public
     *
     * @param bool|string $color The hex color if changing the color of the icon.  Defaults to false.
     *
     * @return string Base64 encoded icon string.
     */
    public static function get_admin_icon_b64(
        $color = false
    ) {

        // Replace the hex color (default was #999999) to %s; it will be replaced by the passed $color

        if ($color) {
            $svg_xml = '<?xml version="1.0" encoding="utf-8"?>' . self::get_admin_icon_svg($color);
            $icon    = sprintf('data:image/svg+xml;base64,%s', base64_encode(sprintf($svg_xml, $color)));
        } else {
            $svg_b64 = 'PHN2ZyBpZD0ic3ZnIiB2ZXJzaW9uPSIxLjEiIHdpZHRoPSI0MDAiIGhlaWdodD0iNDUwIiB2aWV3Qm94PSIwIDAgNDAwIDQ1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgPjxnPjxwYXRoIGZpbGw9IiM5OTk5OTkiIGQ9Ik0xODUuMDAwIDM0LjYzMiBDIDE1My4xNDQgMzkuMTU2LDEzNi42MzMgNDUuNTgzLDExNC40MDAgNjIuMTEzIEMgNjIuOTkzIDEwMC4zMzYsNDYuNDI1IDE3Ni42NzIsNzYuNDg1IDIzNi44MDAgQyA5Ni44MjkgMjc3LjQ5NCwxMzQuMjQ3IDMwMy40NDcsMTgxLjQ0NSAzMDkuNTk5IEMgMTg0Ljk0MCAzMTAuMDU1LDE4OC44ODAgMzEwLjYwMCwxOTAuMjAwIDMxMC44MTEgQyAxOTcuMzg1IDMxMS45NTgsMjExLjEzNSAzMTEuMDUwLDIyNy4wMDAgMzA4LjM4MSBDIDI4NS41MjEgMjk4LjUzOCwzMzAuOTEyIDI1MC41MTgsMzM3LjM4NyAxOTEuNjAwIEMgMzM3LjczOCAxODguNDEwLDMzOC4yMzkgMTg0LjU0MCwzMzguNTAxIDE4My4wMDAgQyAzNDAuNzc0IDE2OS42MjAsMzM2LjkwOSAxNDIuNjMxLDMyOS43OTQgMTIyLjIwMCBDIDMxOC44NzkgOTAuODU3LDI5NS4xODUgNjQuMTYxLDI2NC40MTYgNDguNTM4IEMgMjQxLjg4MCAzNy4wOTQsMjA4LjY0MyAzMS4yNzUsMTg1LjAwMCAzNC42MzIgTTIwNi44MDAgODQuMDE1IEMgMjQwLjQxNyA4Ny4wMjgsMjY1LjM1OCAxMDMuMTA0LDI3OS4zODggMTMwLjgwMiBDIDI5NC41MzYgMTYwLjcwOCwyOTEuNjk3IDIwMC4wMDMsMjcyLjQ4MiAyMjYuNDAwIEMgMjQ2LjI3MyAyNjIuNDA1LDE5MS41NzMgMjcyLjIwOSwxNTEuODU3IDI0OC4wMTggQyAxMTMuNzc0IDIyNC44MjIsMTAwLjUxMCAxNzQuMDQ1LDEyMS4zMjkgMTMxLjE0OSBDIDEzNi40NzkgOTkuOTMxLDE3MS4xMzcgODAuODE5LDIwNi44MDAgODQuMDE1IE0zOS44MDAgMzY3LjE3MyBDIDMzLjc0OCAzNjguNjUxLDMzLjU5OSAzNjkuMjQxLDMzLjYwMiAzOTEuNzUwIEMgMzMuNjA1IDQxMy42MzIsMzMuNzg1IDQxNC40OTcsMzguNjgzIDQxNi4xNjYgQyA0MS4wOTIgNDE2Ljk4NywzNjMuNDM4IDQxNy4xNTUsMzY2LjY4NiA0MTYuMzM3IEMgMzcyLjE4MCA0MTQuOTUzLDM3Mi40OTYgNDEzLjUxNCwzNzIuMzMwIDM5MC42MDAgTCAzNzIuMjAwIDM3Mi42MDAgMzcxLjIwMCAzNzAuODAwIEMgMzcwLjQ2MiAzNjkuNDcxLDM2OS42NzcgMzY4LjczOCwzNjguMjAwIDM2OC4wMDAgTCAzNjYuMjAwIDM2Ny4wMDAgMjAzLjYwMCAzNjYuOTQwIEMgMTE0LjE3MCAzNjYuOTA3LDQwLjQ2MCAzNjcuMDEyLDM5LjgwMCAzNjcuMTczIiBzdHJva2U9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+PC9wYXRoPjwvZz48L3N2Zz4=';
            $icon    = 'data:image/svg+xml;base64,' . $svg_b64;
        }

        return $icon;
    }

    /**
     * Returns the admin icon in SVG format.
     *
     * @since  Unknown
     * @access public
     *
     * @param string $color The hex color if changing the color of the icon.  Defaults to #999999.
     *
     * @return string
     */
    public static function get_admin_icon_svg(
        $color = '#999999'
    ) {
        $svg = '<svg id="svg" version="1.1" width="400" height="450" viewBox="0 0 400 450" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ><g><path fill="%s" d="M185.000 34.632 C 153.144 39.156,136.633 45.583,114.400 62.113 C 62.993 100.336,46.425 176.672,76.485 236.800 C 96.829 277.494,134.247 303.447,181.445 309.599 C 184.940 310.055,188.880 310.600,190.200 310.811 C 197.385 311.958,211.135 311.050,227.000 308.381 C 285.521 298.538,330.912 250.518,337.387 191.600 C 337.738 188.410,338.239 184.540,338.501 183.000 C 340.774 169.620,336.909 142.631,329.794 122.200 C 318.879 90.857,295.185 64.161,264.416 48.538 C 241.880 37.094,208.643 31.275,185.000 34.632 M206.800 84.015 C 240.417 87.028,265.358 103.104,279.388 130.802 C 294.536 160.708,291.697 200.003,272.482 226.400 C 246.273 262.405,191.573 272.209,151.857 248.018 C 113.774 224.822,100.510 174.045,121.329 131.149 C 136.479 99.931,171.137 80.819,206.800 84.015 M39.800 367.173 C 33.748 368.651,33.599 369.241,33.602 391.750 C 33.605 413.632,33.785 414.497,38.683 416.166 C 41.092 416.987,363.438 417.155,366.686 416.337 C 372.180 414.953,372.496 413.514,372.330 390.600 L 372.200 372.600 371.200 370.800 C 370.462 369.471,369.677 368.738,368.200 368.000 L 366.200 367.000 203.600 366.940 C 114.170 366.907,40.460 367.012,39.800 367.173" stroke="none" fill-rule="evenodd"></path></g></svg>';

        return sprintf($svg, $color);
    }

    /**
     * Determines if the current page is part of Lumio Analytics.
     *
     * @since  Unknown
     * @access public
     *
     * @return bool
     */
    public static function is_lumio_page()
    {

        // Gravity Forms pages
        $current_page = trim(strtolower(self::get('page')));
        $wla_pages    = array('wla_settings');

        return in_array($current_page, $wla_pages);
    }

    /**
     * Obtains $_GET values or values from an array.
     *
     * @since  Unknown
     * @access public
     *
     * @param string $name The ID of a specific value.
     * @param array $array An optional array to search through.  Defaults to null.
     *
     * @return string The value.  Empty if not found.
     */
    public static function get($name, $array = null)
    {
        if (! isset($array)) {
            $array = $_GET;
        }

        if (isset($array[$name])) {
            return $array[$name];
        }

        return '';
    }

    /**
     * Adds options & management pages to the admin menu.
     *
     * Run using the 'admin_menu' action.
     */
    public function settings_page()
    {
        $message          = $this->message;
        $wla_register_url = admin_url('admin.php?page=wla_settings&create_account=1');
        if ($this->_is_valid_key) {
            include WLA_DIR . "/templates/lumio_panel.php";
        } elseif (isset($_GET['create_account'])) {
            include WLA_DIR . "/templates/lumio_register.php";
        } else {
            include WLA_DIR . "/templates/lumio_settings.php";
        }
    }

    protected function can_save_key()
    {
        if (isset($_POST['wla_key'])) {
            $this->_key = $_POST['wla_key'];
            if (! self::is_valid_key($this->_key)) {
                return __('Provide a valid key, please', 'wla');
            }
            update_option('wla_key', $this->_key);
            $this->registerLumioIntegration();
        } elseif (isset($_GET['wla_key'])) {
            $this->registerLumioIntegration(false);
            update_option('wla_key', '');
        } else {
            return;
        }
        wp_redirect(admin_url('admin.php?page=wla_settings'));
    }

    protected function registerLumioIntegration($isActive = true)
    {
        global $wp_version;
        $client = new \Lumio\IntegrationAPI\Client();
        $integration = new \Lumio\IntegrationAPI\Model\Integration(array(
            'key'              => $this->_key,
            'url'              => home_url('/'),
            'platform'         => 'WordPress',
            'platform_version' => $wp_version,
            'plugin'           => WLA_NAME,
            'plugin_version'   => WLA_VERSION,
            'status'           => $isActive
        ));

        try {
            $result = $client->registerIntegration($integration);
        } catch (Exception $e) {
            echo 'Exception when calling AdminsApi->getAll: ', $e->getMessage(), PHP_EOL;
        }
    }

    public function invalid_key_admin_menu()
    {
        if (! $this->_is_valid_key) {
            $class   = 'notice notice-warning is-dismissible';
            $url     = admin_url('admin.php?page=wla_settings');
            $message = sprintf(__('Please, set a valid Lumio tracking Key in <a href="%s">settings</a>', 'wla'), $url);

            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        }
    }
}
