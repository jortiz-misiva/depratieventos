<?php
namespace MEC_DIVI_Single_Builder\Core\checkLicense;

// don't load directly.
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
if (!class_exists('\MEC_base')) {
    add_action('admin_notices', [\MEC_DIVI_Single_Builder\Base::$instance, 'MECNotice']);
    return false;
}
/**
 * Webnus MEC envato class.
 * @author Webnus <info@webnus.biz>
 */
class AddonLicense extends \MEC_base
{
    /**
     * The plugin current version
     */
    public $current_version = MECDSBVERSION;

    /**
     * The plugin ite, id
     */
    public $itemid = '04';

    /**
     * The plugin url
     */
    public $itemurl = 'https://webnus.net/modern-events-calendar/addons';

    /**
     * User for cashing directory
     */
    public $purchase_code = '';

    /**
     * Product name
     */
    public $product_name = '';

    /**
     * The plugin remote update path
     */
    public $update_path = '';

    /**
     * Plugin Slug
     */
    public $plugin_slug = MECDSBSLUG  . '/mec-divi-single-builder.php';

    /**
     * Plugin name
     */
    public $slug;

    /**
     * User for cashing directory
     */
    protected $cache_dir = 'cache';

    public $main;
    public $factory;
    public $settings;

    /**
     *  MEC update constructor
     */
    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();

        // Import MEC Factory
        $this->factory = $this->getFactory();

        // MEC Settings
        $options = get_option(MECDSBOPTIONS);

        // Set user purchase code
        $this->set_purchase_code(isset($options['purchase_code']) ? $options['purchase_code'] : '');
        $this->set_product_name(isset($options['product_name']) ? $options['product_name'] : '');

        // Plugin Slug
        $this->slug = MECDSBSLUG;

        // updating checking
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));

        // information checking
		add_filter('plugins_api', array($this, 'check_info'), 10, 3);
    }

    /**
     * Get API URL.
     * @author Webnus <info@webnus.biz>
     * @param string $api_url
     */
    public static function get_api_url()
    {
        if (get_headers('https://webnus.biz')[0] != 'HTTP/1.1 200 OK') {
            $api_url = 'https://webnus.net/api';
        } else {
            $api_url = 'http://webnus.biz/webnus.net';
        }

        return $api_url;
    }

    /**
     * Set purchase code.
     * @author Webnus <info@webnus.biz>
     * @param string $purchase_code
     */
    public function set_purchase_code($purchase_code)
    {
        $this->purchase_code = $purchase_code;
    }

    /**
     * Set product_name.
     * @author Webnus <info@webnus.biz>
     * @param string $product_name
     */
    public function set_product_name($product_name)
    {
        $this->product_name = $product_name;
    }

    /**
     * Set update path.
     * @author Webnus <info@webnus.biz>
     * @param string $update_path
     */
    public function set_update_path($update_path)
    {
        $this->update_path = $update_path;
    }

    /**
     * GET purchase code.
     * @author Webnus <info@webnus.biz>
     */
    public function get_purchase_code()
    {
        return $this->purchase_code;
    }

    /**
     * GET product_name.
     * @author Webnus <info@webnus.biz>
     */
    public function get_product_name()
    {
        return $this->product_name;
    }

    /**
     * Get update path.
     * @author Webnus <info@webnus.biz>
     */
    public function get_update_path()
    {
        return $this->update_path;
    }

    /**
     * Add our self-hosted autoupdate plugin to the filter transien
     * @author Webnus <info@webnus.biz>
     * @param object $transient
     * @return object
     */
    public function check_update($transient)
    {
        if(empty($transient->checked)) return $transient;

        // Get the remote version
        $version = (isset($this->get_MEC_info('version')->version) and !empty($this->get_MEC_info('version')->version)) ? json_decode(json_encode($this->get_MEC_info('version')->version), true) : '';

        // Set mec update path
        $dl_link = !is_null($this->get_MEC_info('dl')) ? $this->set_update_path($this->get_MEC_info('dl')) : NULL;

        // If a newer version is available, add the update
        if(version_compare($this->current_version, $version, '<'))
        {
            $obj = new \stdClass();
            $obj->id = $this->itemid;
            $obj->slug = $this->slug;
            $obj->plugin = $this->plugin_slug;
            $obj->requires = '4.0';
            $obj->tested = '4.9';
            $obj->new_version = $version;
            $obj->url = $this->itemurl;
            $obj->package = $this->get_update_path();
            $obj->sections = array
            (
                'description' => '',
                'changelog' => ''
            );

            $transient->response[$this->plugin_slug] = $obj;
        }
        elseif(isset($transient->response[$this->plugin_slug]))
        {
            unset($transient->response[$this->plugin_slug]);
        }

        return $transient;
    }

    /**
     * Add our self-hosted description to the filter
     * @author Webnus <info@webnus.biz>
     */
    public function check_info($false, $action, $arg)
    {
        if(isset($arg->slug) and $arg->slug === $this->slug)
        {
            $information = $this->get_MEC_info('info');
            if($information->item)
            {
                $arg->fields->short_description = true;
                $arg->fields->description = true;
                $arg->fields->sections = true;
                $arg->slug = $this->slug;
                $arg->plugin_name = MECDSBNAME;
                $arg->author = 'webnus';
                $arg->homepage = $this->itemurl;
                $arg->banners['low'] = 'https://0.s3.envato.com/files/202920118/mec-preview1.png';

                return $arg;
            }
        }

        return false;
    }

    /* Save version to database */
    public function addons_version_in_database() {
        $addons_save_version_date = get_option('addons_save_version_date');
        if (!$addons_save_version_date) {
            $JSON = wp_remote_retrieve_body(wp_remote_get(self::get_api_url() . '/addons-api/version', array(
                'body' => null,
                'timeout' => '120',
                'redirection' => '10',
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
            )));
            $JSON = json_decode($JSON);
            var_dump($JSON);
            update_option('addons_save_version_number', $JSON->version);
            update_option('addons_save_version_date', date("Y-m-d"));
            return true;
        } else {
            if ( strtotime(date("Y-m-d")) > strtotime($addons_save_version_date) ) {
                $JSON = wp_remote_retrieve_body(wp_remote_get(self::get_api_url() . '/addons-api/version', array(
                    'body' => null,
                    'timeout' => '120',
                    'redirection' => '10',
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
                )));
                $JSON = json_decode($JSON);
                update_option('addons_save_version_number', $JSON->version);
                update_option('addons_save_version_date', date("Y-m-d"));
                return true;
            }
            return false;

        }
    }

    /**
     * Return details from envato
     * @author Webnus <info@webnus.biz>
     * @param string $type
     * @return mixed
     */
    public function get_MEC_info($type = 'dl')
    {
        // setting the header for the rest of the api
        $code = $this->get_purchase_code();
        $product_name = $this->get_product_name();
        $url  = get_home_url();

        if($type == 'remove') {
            $verify_url = 'https://webnus.net/api/remove?id='.$code;
        } elseif($type == 'dl') {
            $verify_url = self::get_api_url() . '/addons-api/verify?item_name=' . urlencode($product_name) . '&id=' . $code . '&url=' . $url;
        } elseif($type == 'version') {
            if ( $this->addons_version_in_database() ) {
                $verify_url = self::get_api_url() . '/addons-api/version?item_name=' . $product_name;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }

        $JSON = wp_remote_retrieve_body(wp_remote_get($verify_url, array(
            'body' => null,
            'timeout' => '120',
            'redirection' => '10',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
        )));
        if($JSON != '') return json_decode($JSON);
        else return false;
    }
}