<?php

/**
 *	mec external database model
 *	any access to database uses the class
 *
 * @category mec-external-admin
 * @package mec-external
 * @author Webnus Team <info@webnus.net>
 * @version 0.0.1
 */
class MEC_Sites_Model {

	/**
	 * table name
	 * @var string
	 */
	private $table;

	/** @var array all table fields */
	public $fields = array();

	/**
	 * wpdb global variable
	 * @var object
	 */
	private $db;

	/**
	 * post_id on the crud usage
	 * @var integer
	 */
	private $post_id;

	/**
	 * row type, event/calendar
	 * @var string
	 */
	private $type;

	function __construct($post_id = null, $type = null) {

		global $wpdb;
		$this->db = $wpdb;

		$this->table = $wpdb->prefix . 'mec_external_sites';

		$this->post_id = $post_id;

		$this->type = $type;
	}

	/**
	 * checked exists the domain on the database
	 * @param  string $d domain name
	 * @return integer    count of the domains
	 */
	private function exists($d) {
		$ex_sql = "SELECT COUNT(id) FROM `{$this->table}` WHERE `domain`='{$d}' AND `post_id`='{$this->post_id}'";
		return (int) $this->db->get_var($ex_sql);
	}

	/**
	 * get the one row domain access
	 * @param  integer $id table promary field
	 * @return object     if exists return object or null
	 */
	public function one($id) {
		return $this->db->get_row("SELECT * FROM {$this->table} WHERE id={$id} AND post_id={$this->post_id}");
	}

	/**
	 * process the form post submit
	 * @return bool if the success process return true or false on failed request
	 */
	public function process() {

		// chcked nonce the csrf protection
		if (
			isset($_POST['mec_external_sites_nonce_field'])
			&& !wp_verify_nonce($_POST['mec_external_sites_nonce_field'], 'mec_external_sites_action')
		) {
			return false;
		}

		$submit = isset($_POST['submit']) ? $_POST['submit'] : null;
		$sites = isset($_POST['sites']) ? $_POST['sites'] : null;
		$any = isset($_POST['any']) ? $_POST['any'] : 'no';

		if (!$submit) {
			// no request and not changed data
			return true;
		}

		MDEVLibs::wlog("submit thy any to:{$any}");

		if (!add_post_meta($this->post_id, 'mec_external_any', $any, true)) {
			update_post_meta($this->post_id, 'mec_external_any', $any);
		}

		if (empty($sites)) {
			return true;
		}

		foreach ($sites as $k => $site) {
			MDEVLibs::wlog("TheSite: {$site}");

			$d = MDEVLibs::get_domain($site);

			if (!$d) {
				MDEVLibs::wlog("The Site:{$site} cannot extract domain, Failed input");
				continue;
			}

			if ($this->exists($d) > 0) {
				MDEVLibs::wlog("The Site:{$d} exists on valida sites");
				continue;
			}

			$data = array(
				'post_id' => $this->post_id,
				'created_at' => current_time('mysql'),
				'domain' => $d,
				'type' => $this->type,
			);

			if ($this->one($k)) {
				$this->db->update(
					$this->table,
					$data,
					array('id' => $k)
				);
				continue;
			}

			$this->db->insert($this->table, $data);
			$id = $this->db->insert_id;
			// MDEVLibs::wlog("Inserted:{$id}, Data=".print_r($data,true).$this->db->print_error());
		}
	}

	public function delete($id) {
		return $this->db->delete($this->table, array('id' => $id));
	}

	public function any() {
		$a = get_post_meta($this->post_id, 'mec_external_any', true);
		return $a != null ? $a : 'no';
	}

	public function access() {
		if ($this->any() == 'yes') {
			MDEVLibs::wlog("Success any domain access the post:{$this->post_id}");
			return true;
		}

		$host = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
		if (!$host) {
			MDEVLibs::wlog("Cannot detect the domain");
			return false;
		}

		$d = MDEVLibs::get_domain($host);
		if (!$d) {
			MDEVLibs::wlog("Cannot extract domain from host:{$host}");
			return false;
		}

		return $this->exists($d) > 0;

	}

	public function sites() {
		$sql = "SELECT `domain`,`id` FROM `{$this->table}` WHERE `post_id`='{$this->post_id}' AND `type`='{$this->type}'";
		return $this->db->get_results($sql, ARRAY_A);
	}
}
