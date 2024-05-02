<?php


/**
 *	mec external ajax request handler
 *	for delete one row site access handle and delete from table
 * 
 * @category mec-external-admin
 * @package mec-external
 * @author Webnus Team <info@webnus.net>
 * @version 0.0.1
 */
class MEC_External_Ajax
{

	/**
	 * delete one site by id from the table
	 * @return json json object on delete or failed error
	 */
	public function delsite(){
		$id = isset($_POST['id']) ? $_POST['id'] : null;

		if (empty($id)) {
			return wp_send_json_error('id not found');
		}

		$model = new MEC_Sites_Model();
		if(!$model->delete($id)){
			return wp_send_json_error('Server Error!');
		}

		wp_send_json_success('success');
	}
	
	public function init()
	{
		add_action('wp_ajax_mec_external_delsite', array($this, 'delsite'));
	}
}