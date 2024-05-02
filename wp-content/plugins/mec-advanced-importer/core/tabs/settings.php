<?php
namespace MEC_Advanced_Importer\Core\Tabs;

class Settings {
	public $name = 'Settings';
	public $main;
	public $message = null;
	public $reset_form = false;

	function __construct() {
		$this->main = \MEC::getInstance('app.libraries.main');
	}

	public function process_form() {
		$section = isset($_POST['mec-advimp-setting-section']) ? $_POST['mec-advimp-setting-section'] : null;
		if (!$section) {
			return false;
		}
		$this->section = $section;

		// Verify the nonce before proceeding.
		if (!isset($_POST['advimp_settings_nonce']) || !wp_verify_nonce($_POST['advimp_settings_nonce'], MEC_ADVANCED_IMPORTER_DIR)) {
			return false;
		}

		// check user capability to edit post
		if (!current_user_can('manage_options')) {
			return false;
		}

		$id = isset($_POST['mec-advimp-setting-id']) && !empty($_POST['mec-advimp-setting-id']) ? $_POST['mec-advimp-setting-id'] : null;
		$cur = $this->get_settings();

		$update = false;
		foreach ($cur as $k => $v) {
			if ("{$v['id']}" == "{$id}") {
				$cur[$_POST[$section]['id']] = $_POST[$section];
				$update = true;
				$this->message = __('Success Update Account', 'mec-advanced-importer');
				$this->reset_form = true;
				break;
			}
		}

		if ($update === false) {
			$cur[$_POST[$section]['id']] = $_POST[$section];
			$this->message = __('Success Add Account', 'mec-advanced-importer');
		}

		$this->save_settings($cur);

		return true;
	}

	public function get_settings() {
		$name = "mec_advimp_config_{$this->section}";
		return get_option($name, array());
	}
	public function save_settings($setting) {
		$name = "mec_advimp_config_{$this->section}";
		update_option($name, $setting);
	}

	public function get_id() {
		return md5(mt_rand(1, 999) . time() . mt_rand(1000, 9999));
	}

	public function delete_account($id) {
		$options = $this->get_settings();
		foreach ($options as $k => $v) {

			if ("{$v['id']}" == "{$id}") {
				unset($options[$k]);
				$this->save_settings($options);
				return true;
			}
		}

		return false;

	}

	public function content() {
		$save = $this->process_form();
		$s = array();
		$ctab = isset($_GET['ctab']) ? $_GET['ctab'] : 'facebook';
		$this->section = $ctab;

		$action = isset($_REQUEST['mec_advimp_action']) ? $_REQUEST['mec_advimp_action'] : null;
		$id = isset($_GET['id']) ? $_GET['id'] : null;
		$options = $this->get_settings();
		switch ($action) {
		case 'mec_advimp_setting_delete':
			$this->delete_account($id);
			break;

		case 'mec_advimp_setting_edit':

			if ($this->reset_form == false) {

				foreach ($options as $k => $v) {

					if ("{$v['id']}" == "{$id}") {
						$s = $v;
						break;
					}
				}
			}
			break;
		case 'update_general':
			$general = isset($_POST['mecadvimpgeneral'])?$_POST['mecadvimpgeneral']:null;
			update_option( 'mecadvimpgeneral', $general );
			$this->message = __('Update General Settings.','mec-advanced-importer');

		break;
		}

		$settings_tab = array(
			'facebook' => __('Facebook', 'mec-advanced-importer'),
			'eventbrite' => __('Eventbrite', 'mec-advanced-importer'),
			'meetup' => __('Meetup', 'mec-advanced-importer'),
			'google' => __('Google', 'mec-advanced-importer'),
			'mecapi' => __('MEC API', 'mec-advanced-importer'),
			'general' => __('General', 'mec-advanced-importer'),
		);

		$reset_url = null;
		if ($this->reset_form) {
			$args = array(
				'page' => 'MEC-advimp',
				'tab' => 'MEC-Settings',
				'ctab' => $ctab,
			);
			$reset_url = add_query_arg($args, admin_url('admin.php'));
		}

		?>

<input type="hidden" id="mec-advimp-reset-url" value="<?php if ($this->reset_form) {
			echo $reset_url;
		}
		?>">

<div class="import-content w-clearfix extra">
   <div class="mec-facebook-import">

<?php if ($this->message != null): ?>
<div id="mec-advinp-alert-success" class="alert success">
   <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
   <strong>
    <?php _e('Success', 'mec-advanced-importer');?>
   </strong>
   <span id="mec-advimp-alert-success">
    <?php echo $this->message; ?>
     <b id="mec-advimp-alert-success-message"></b>
   </span>
 </div>
<?php endif;?>
      <div class="wp-filter">
          <ul class="filter-links">
         <?php
foreach ($settings_tab as $tab => $title): ?>

<?php
$args = array(
			'page' => 'MEC-advimp',
			'tab' => 'MEC-Settings',
			'ctab' => $tab,
		);
		$url = add_query_arg($args, admin_url('admin.php'));
		?>
         <li>
            <a class="<?php if ($ctab == $tab) {echo 'current';}?>"
               href="<?php echo esc_url($url); ?>">
            <?php echo esc_html($title); ?>
            </a>
          </li>
       <?php endforeach;?>
         </ul>
      </div>
      <?php

		if (!class_exists('WP_List_Table')) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		include MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'settings-table.php';
		$table = new \MEC_Advanced_Importer_Settings_Table();
		$table->section = $ctab;
		$table->setting_class = $this;

		$content = MEC_ADVANCED_IMPORTER_CONTENET_DIR . DS . 'settings' . DS . $ctab . '.php';

		if (file_exists($content)) {
			include $content;
		} else {
			echo 'Content Tab Not Exists!';
		}

		?>



   </div>
</div>


		<?php
}
}
