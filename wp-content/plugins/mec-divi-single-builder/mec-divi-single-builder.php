<?php
/**
 * Plugin Name: Divi Single Builder for MEC
 * Plugin URI: https://webnus.net/
 * Description:       Use this Add-on to build your single event page layout in Divi Page Builder. It allows you to use many different type of fields and rearrange them by drag and drop and modify their styles.
 * Version:           1.0.3
 * Author:            Webnus
 * Author URI:        https://webnus.net/
 * Text Domain:       mec-divi-single-builder
 * Domain Path:       /languages
 */

namespace MEC_DIVI_Single_Builder;

// Don't load directly
if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}
/**
 *   Base.
 *
 *   @author     Webnus <info@webnus.biz>
 *   @package     Modern Events Calendar
 *   @since     1.0.0
 */
class Base
{

	/**
	 *  Instance of this class.
	 *
	 *  @since   1.0.0
	 *  @access  public
	 *  @var     MEC_DIVI_Single_Builder
	 */
	public static $instance;

	/**
	 *  Provides access to a single instance of a module using the Singleton pattern.
	 *
	 *  @since   1.0.0
	 *  @return  object
	 */
	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		if (defined('MECDSBVERSION')) {
			return;
		}
		self::settingUp();
		self::preLoad();
		self::setHooks($this);

		do_action('MEC_DIVI_Single_Builder_init');
	}

	/**
	 *  Global Variables.
	 *
	 *  @since   1.0.0
	 */
	public static function settingUp()
	{
		define('MECDSBVERSION', '1.0.3');
		define('MECDSBDIR', plugin_dir_path(__FILE__));
		define('MECDSBURL', plugin_dir_url(__FILE__));
		define('MECDSBASSETS', MECDSBURL . 'assets/');
		define('MECDSBNAME' , 'Divi Single Builder');
		define('MECDSBSLUG' , 'mec-divi-single-builder');
		define('MECDSBOPTIONS' , 'mec_divi_single_builder_options');
		define('MECDSBTEXTDOMAIN' , 'mec-divi-single-builder');

		if (!defined('DS')) {
			define('DS', DIRECTORY_SEPARATOR);
		}
	}

	/**
	 * Is MEC installed?
	 *
	 * @since     1.0.0
	 */
	public static function is_mec_installed()
	{
		$file_path         = 'modern-events-calendar/mec.php';
		$file_path_lite    = 'modern-events-calendar-lite/modern-events-calendar-lite.php';
		$installed_plugins = get_plugins();
		if ( isset( $installed_plugins[ $file_path ] ) ) {
			return 'pro';
		} elseif ( isset( $installed_plugins[ $file_path_lite ] ) )  {
			return 'lite';
		}
	}

	/**
	 *  Set Hooks
	 *
	 *  @since     1.0.0
	 */
	public static function setHooks($This)
	{
		add_action( 'wp_loaded', [ $This, 'load_languages' ] );
	}

	/**
	* Load Languages
	*
	* @since 1.0.0
	*/
	public function load_languages() {
		$locale = apply_filters('plugin_locale', get_locale(), 'mec-divi-single-builder');

		// WordPress language directory /wp-content/languages/mec-en_US.mo
		$language_filepath = MECDSBDIR . 'languages' . DIRECTORY_SEPARATOR . 'mec-divi-single-builder-' . $locale . '.mo';
		// If language file exists on WordPress language directory use it
		if (file_exists($language_filepath)) {
			load_textdomain('mec-divi-single-builder', $language_filepath);
		} else {
			load_plugin_textdomain('mec-divi-single-builder', false, dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);
		}
	}

		/**
	 *  MEC Version Admin Notice
	 *
	 *  @since     1.0.0
	 */
	public function MECVersionAdminNotice($type = false)
	{
		$screen = get_current_screen();
		if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
			return;
		}

		$plugin = 'modern-events-calendar/mec.php';
		$plugin_lite = 'modern-events-calendar-lite/modern-events-calendar-lite.php';

		if (!current_user_can('install_plugins')) {
			return;
		}

		if ( $this->is_mec_installed() != 'lite' && $this->is_mec_installed() != 'pro' ) {
			$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=modern-events-calendar-lite' ), 'install-plugin_modern-events-calendar-lite' );
			$message .= '<div class="notice notice-error is-dismissible"><p>' . __( ' MEC Divi Single Builder is not working because you need to install the Modern Events Calendar Lite plugin', 'mec-divi-single-builder' ) . '</p>';
			$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Modern Events Calendar Lite Now', 'mec-divi-single-builder' ) ) . '</p></div>';
		} elseif ( $this->is_mec_installed() != 'lite' && $this->is_mec_installed() == 'pro' && !is_plugin_active( $plugin ) && is_plugin_active( $plugin_lite ) )	{
			$this->check_mec_version();
		} elseif ( $this->is_mec_installed() != 'lite' &&  $this->is_mec_installed() == 'pro' && !is_plugin_active( $plugin ) && !is_plugin_active( $plugin_lite )  )	{
			$install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=modern-events-calendar'), 'install-plugin_' . $plugin);
			$message     = '<p>' . __('MEC Divi Single Builder is not working because you need to install latest version of Modern Events Calendar plugin', 'mec-divi-single-builder') . '</p>';
			$message    .= esc_html__('Minimum version required') . ': <b> 4.2.3 </b>';
			$message    .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, __('Update Modern Events Calendar Now', 'mec-divi-single-builder')) . '</p>';
			$this->check_mec_version();
		} elseif ( $this->is_mec_installed() == 'lite' && $this->is_mec_installed() != 'pro' &&  !is_plugin_active( $plugin_lite ) ) {
			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin_lite . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin_lite );
			$message  .= '<div class="notice notice-error is-dismissible"><p>' . __( NS\PLUGIN_ORG_NAME . ' is not working because you need to activate the Modern Events Calendar Lite plugin.', 'mec-divi-single-builder' ) . '</p>';
			$message  .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Modern Events Calendar Lite Now', 'mec-divi-single-builder' ) ) . '</p></div>';
			$this->check_mec_version();
		}

		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo $message; ?></p>
		</div>
	<?php

		}

		/**
		 * Plugin Requirements Check
		 *
		 * @since 1.0.0
		 */
		public static function checkPlugins()
		{
			if (!function_exists('is_plugin_active')) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if(!self::$instance) {
				self::$instance = static::instance();
			}
			// if (!is_plugin_active('modern-events-calendar/mec.php') && !class_exists('\MEC')) {
			// 	add_action('admin_notices', [self::$instance, 'MECNotice']);
			// 	return false;
			// } else {
			// 	// if (!defined('MEC_VERSION')) {
			// 	// 	$plugin_data = get_plugin_data(realpath(WP_PLUGIN_DIR . '/modern-events-calendar/mec.php'));
			// 	// 	$version     = str_replace('.', '', $plugin_data['Version']);
			// 	// } else {
			// 	// 	$version = str_replace('.', '', MEC_VERSION);
			// 	// }
			// 	// if ($version <= 421) {
			// 	// 	add_action('admin_notices', [self::$instance, 'MECVersionAdminNotice'], 'version');
			// 	// 	return false;
			// 	// }
			// }

			$plugin = 'modern-events-calendar/mec.php';
			$plugin_lite = 'modern-events-calendar-lite/modern-events-calendar-lite.php';
			$mec_plugin_version = self::is_mec_installed();
	
			if ( $mec_plugin_version == 'pro' ) {
				$plugin_data = get_plugin_data( realpath( WP_PLUGIN_DIR . '/modern-events-calendar/mec.php' ) );
				$version     = str_replace( '.', '', $plugin_data['Version'] );
				if ( $version <= 431 ) {
					$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=modern-events-calendar' ), 'install-plugin_' . $plugin );
					$this->$message  .= '<div class="notice notice-error is-dismissible"><p>' . __( 'This Addon is not working because you need to install latest version of Modern Events Calendar plugin', 'mec-divi-single-builder' ) . '</p>';
					$this->$message  .= esc_html__( 'Minimum version required' ) . ': <b> 4.3.1 </b>';
					$this->$message  .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Update Modern Events Calendar Now', 'mec-divi-single-builder' ) ) . '</p></div>';
				}
			} elseif ( $mec_plugin_version == 'lite' ) { 
				$plugin_data = get_plugin_data( realpath( WP_PLUGIN_DIR . '/modern-events-calendar-lite/modern-events-calendar-lite.php' ) );
				$version     = str_replace( '.', '', $plugin_data['Version'] );
				if ( $version <= 431 ) {
					$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=modern-events-calendar-lite' ), 'install-plugin_' . $plugin_lite );
					$this->$message  .= '<div class="notice notice-error is-dismissible"><p>' . __( 'This Addon is not working because you need to install latest version of Modern Events Calendar Lite plugin', 'mec-divi-single-builder' ) . '</p>';
					$this->$message  .= esc_html__( 'Minimum version required' ) . ': <b> 4.3.1 </b>';
					$this->$message  .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Update Modern Events Calendar Lite Now', 'mec-divi-single-builder' ) ) . '</p></div>';
				} 
			}

			return true;
		}

		/**
		 * Send Admin Notice (MEC)
		 *
		 * @since 1.0.0
		 */
		public function MECNotice($type = false)
		{
			$screen = get_current_screen();
			if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
				return;
			}

			$plugin = 'modern-events-calendar/mec.php';
			$plugin_lite = 'modern-events-calendar-lite/modern-events-calendar-lite.php';

			if ($this->is_mec_installed()  == 'pro'  ) {
				if (!current_user_can('activate_plugins')) {
					return;
				}
				$activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin);
				$message        = '<p>' . __('MEC Divi Single Builder is not working because you need to activate the Modern Events Calendar plugin.', 'mec-divi-single-builder') . '</p>';
				$message       .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $activation_url, __('Activate Modern Events Calendar Now', 'mec-divi-single-builder')) . '</p>';
			} elseif ( $this->is_mec_installed()  == 'lite'  ) {
				if (!current_user_can('activate_plugins')) {
					return;
				}
				$activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin_lite . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin_lite);
				$message        = '<p>' . __('MEC Divi Single Builder is not working because you need to activate the Modern Events Calendar plugin.', 'mec-divi-single-builder') . '</p>';
				$message       .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $activation_url, __('Activate Modern Events Calendar Lite Now', 'mec-divi-single-builder')) . '</p>';
			} else {
				if (!current_user_can('install_plugins')) {
					return;
				}
				$install_url = 'https://webnus.net/modern-events-calendar/';
				$message     = '<p>' . __('MEC Divi Single Builder is not working because you need to install the Modern Events Calendar plugin', 'mec-divi-single-builder') . '</p>';
				$message    .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, __('Install Modern Events Calendar Now', 'mec-divi-single-builder')) . '</p>';
			}

			?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo $message; ?></p>
		</div>
<?php

	}

	/**
	 *  PreLoad
	 *
	 *  @since     1.0.0
	 */
	public static function preLoad()
	{
		if(static::checkPlugins()) {
			include_once MECDSBDIR . DS . 'core' . DS . 'autoloader.php';
		}
	}
} //Base

Base::instance();
