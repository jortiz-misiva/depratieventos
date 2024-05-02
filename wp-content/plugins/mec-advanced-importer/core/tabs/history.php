<?php

namespace MEC_Advanced_Importer\Core\Tabs;

class History {
	public $name = 'History';

	public $table;

	public function __construct() {
		if (!class_exists('WP_List_Table')) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		$content = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'history-table.php';
		include $content;

		$this->table = new \MEC_Advanced_Importer_History_Table();
	}

	public function connect() {
		return true;
	}

	public function content() {

		$this->table->prepare_items();
		echo '<form method="post">';
		echo $this->table->display();
		echo '</form>';

	}
}
