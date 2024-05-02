<?php

function bp_mec_is_mec_enabled($default = 0) {
	return (bool) bp_get_option('bp-mec-enable', $default);
}

function bp_mec_is_mec_groups_enabled($default = 0, $is_checked_component = false) {

	return (bool) bp_get_option('bp-mec-enable-groups', $default);
}

function bp_mec_is_mec_events_menu_enabled($default = 0, $is_checked_component = false) {

	return (bool) bp_get_option('bp-mec-enable-events-menu', $default);
}

function bp_mec_is_mec_filters_enabled($default = 0) {
	return (bool) bp_get_option('bp-mec-enable-filters', $default);
}

function bp_mec_show_preview_in_activity_enabled($default = 0) {
	return (bool) bp_get_option('bp-mec-show-event-preview-in-activity', $default);
}

function bp_mec_is_hide_btn_back_to_events_list($default = 0) {
	return (bool) bp_get_option('bp-mec-hide-btn-back-to-events-list-in-fes-form', $default);
}

function bp_mec_echo_fes_form_styles_and_scripts($default = 0) {

	$hide = bp_mec_is_hide_btn_back_to_events_list();
	if( !$hide ){
		return;
	}

	?>
	<style type="text/css">
		.mec-fes-form-top-actions{
			display: none !important;
		}
	</style>
	<?php

}

function bp_mec_is_mec_assign_groups_enabled($default = 0) {
	return (bool) bp_get_option('bp-mec-enable-assign-groups', $default);
}

function bp_mec_get_settings( $key, $default = '' ) {

	return bp_get_option( "bb_mec_$key", $default );
}

function mec_bp_get_all_groups_formated($event_id) {
	$metas_assigned = BP_MEC_Group_Helper::get_event_groups( $event_id );
	$ret = '';
	$user_id = get_current_user_id();
	if ($metas_assigned != null) {
		foreach ($metas_assigned as $k => $group) {

			$ret .= '<span class="mec-bp-groups-assign-list">';
			$ret .= $group['name'];

			if (bp_mec_is_user_can_event_change($group['id']) == true) {
				$ret .= '<a href="#" onclick="return mec_bp_Events_Assign(\'' . $event_id . '\',\'' . $group['id'] . '\',\'del\')">x</a>';
			}

			$ret .= '</span>';
		}
	}

	return $ret;
}

/**
 * Output the 'checked' value, if needed, for a given status on the group admin screen
 *
 * @since 1.0.0
 *
 * @param string      $setting The setting you want to check against ('members',
 *                             'mods', or 'admins').
 * @param object|bool $group   Optional. Group object. Default: current group in loop.
 */
function bp_mec_group_show_manager_setting($setting, $group = false) {
	$group_id = isset($group->id) ? $group->id : $group;

	$status = bp_mec_group_get_manager($group_id);

	if ($setting === $status) {
		echo ' checked="checked"';
	}
}

/**
 * Check group mec is setup or not.
 *
 * @since 1.0.0
 * @param int $group_id Group ID.
 *
 * @return bool Returns true if mec is setup.
 */
function bp_mec_is_group_setup($group_id) {

	if (!bp_is_active('groups')) {
		return false;
	}

	$user_id = bp_loggedin_user_id();
	if (!$user_id) {
		return false;
	}

	if (groups_is_user_mod($user_id, $group_id)) {
		return true;
	}

	if(groups_is_user_creator($user_id,$group_id)){
		return true;
	}

	$group_mec = groups_get_groupmeta($group_id, 'bp-group-mec', true);

	if (!$group_mec) {
		return false;
	}

	return true;
}

/**
 * Get the mec manager of a group.
 *
 * This function can be used either in or out of the loop.
 *
 * @since 1.0.0
 *
 * @param int|bool $group_id Optional. The ID of the group whose status you want to
 *                           check. Default: the displayed group, or the current group
 *                           in the loop.
 * @return bool|string Returns false when no group can be found. Otherwise
 *                     returns the group mec manager, from among 'members',
 *                     'mods', and 'admins'.
 */
function bp_mec_group_get_manager($group_id = false) {
	global $groups_template;

	if (!$group_id) {
		$bp = buddypress();

		if (isset($bp->groups->current_group->id)) {
			// Default to the current group first.
			$group_id = $bp->groups->current_group->id;
		} elseif (isset($groups_template->group->id)) {
			// Then see if we're in the loop.
			$group_id = $groups_template->group->id;
		} else {
			return false;
		}
	}

	$manager = groups_get_groupmeta($group_id, 'bp-group-mec-manager', true);

	// Backward compatibility. When '$manager' is not set, fall back to a default value.
	if (!$manager) {
		$manager = apply_filters('bp_mec_group_manager_fallback', 'admins');
	}

	/**
	 * Filters the album status of a group.
	 *
	 * @since 1.0.0
	 *
	 * @param string $manager Membership level needed to manage albums.
	 * @param int    $group_id      ID of the group whose manager is being checked.
	 */
	return apply_filters('bp_mec_group_get_manager', $manager, $group_id);
}

function bp_mec_is_user_can_event_change($group_id = null) {
	$user_id = get_current_user_id();
	$manager = bp_mec_group_get_manager($group_id);
	if (bp_mec_groups_can_user_manage_mec($user_id, $group_id, $manager) === true) {
		return true;
	}

	return false;
}

/**
 * Check whether a user is allowed to manage mec meetings in a given group.
 *
 * @since 1.0.0
 *
 * @param int $user_id ID of the user.
 * @param int $group_id ID of the group.
 * @return bool true if the user is allowed, otherwise false.
 */
function bp_mec_groups_can_user_manage_mec($user_id, $group_id, $manager = null) {
	$is_allowed = false;

	if (!is_user_logged_in()) {
		return false;
	}

	// Site admins always have access.
	if (bp_current_user_can('bp_moderate')) {
		return true;
	}

	if (!groups_is_user_member($user_id, $group_id)) {
		return false;
	}

	if ($manager === null && strlen($manager) <= 0) {
		$manager = bp_mec_group_get_manager($group_id);
	}

	$is_admin = groups_is_user_admin($user_id, $group_id);
	$is_mod = groups_is_user_mod($user_id, $group_id);

	if ('members' === $manager) {
		$is_allowed = true;
	} elseif ('mods' === $manager && ($is_mod || $is_admin)) {
		$is_allowed = true;
	} elseif ('admins' === $manager && $is_admin) {
		$is_allowed = true;
	}

	return apply_filters('bp_mec_groups_can_user_manage_event', $is_allowed);
}

function bp_mec_settings_tutorial() {
	return '';
}

function bp_mec_is_active_top_plugins() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if (is_plugin_active('buddyboss-platform/bp-loader.php') &&
		is_plugin_active('modern-events-calendar/mec.php') || is_plugin_active('buddyboss-platform/bp-loader.php') &&
		is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php')) {
		return true;
	}

	return false;
}

/**
 * Return Groups
 *
 * @param array $args
 *
 * @return array
 */
function bp_mec_get_groups( $args = array() ){
	$defaults = array(
		'per_page' => -1,
		'show_hidden' => true,
	);

	if( !current_user_can( 'administrator' ) ){

		$defaults['user_id'] = bp_loggedin_user_id();
	}

	$args = wp_parse_args( $args, $defaults );

	$groups = groups_get_groups( $args );
	return isset($groups['groups']) && count($groups['groups'])>0?$groups['groups']:null;
}

function mec_bb_get_edit_or_create_event_link( $event_id = 0, $check_capability = true ){

	if ( $event_id!=0 && !mec_bb_current_user_can_edit_event( $event_id )){

		return '';
	}

	$group_id = null;
	if(bp_is_groups_component() && function_exists('bp_get_current_group_id')){

		$group_id = bp_get_current_group_id();
	}

	if($group_id != null){

		$url = trailingslashit( bp_get_group_permalink( groups_get_group( $group_id ) ) . 'mec-group/create-event/' );
	}else{

		$url = bp_loggedin_user_domain() . 'mec-main/create-event';
	}


	if( $event_id ){

		$url .= '?post_id=' . $event_id;
	}

	return esc_url( $url );
}

function mec_bb_current_user_can_edit_event( $event_id ){

	return (new \MEC_feature_fes())->current_user_can_upsert_event( $event_id );
}
