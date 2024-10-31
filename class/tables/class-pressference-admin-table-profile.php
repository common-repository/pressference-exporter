<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WP_List_Table')) {
	include_once(dirname(PRESSFERENCE_FILE)."/class/core/wp-admin/class-wp-list-table.php");
}

if (!class_exists('Pressference_Profile')) {
	include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-profile.php");
}

require_once(dirname(PRESSFERENCE_FILE)."/class/core/class-pressference-exporter-handling.php");

class Pressference_Admin_Table_Profile extends WP_List_Table {
	protected $wpdb;
	protected $list_table_type = 'edit-exporter_profile';
	protected $post_type = 'exporter_profile';
	private $profile;
	private $exporter_handling;

	public function __construct() {
		global $wpdb;
		
		$this->wpdb = $wpdb;
		$this->screen = get_current_screen();
		$this->exporter_handling = new Pressference_Exporter_Handling();
		$this->profile = new Pressference_Profile();
		parent::__construct(
			array(
				'singular' => 'exporter_profile',
				'plural' => 'exporter_profiles',
				'ajax' => true
			)
		);

		add_filter('view_mode_post_types', array($this, 'disable_view_mode'));
		add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'));
		add_filter('views_'.$this->list_table_type, array($this, 'get_views'));
		add_filter('default_hidden_columns', array($this, 'default_hidden_columns'), 10, 2);
		// add_filter('list_table_primary_column', array($this, 'list_table_primary_column'), 10, 2);
		add_filter('manage_'.$this->list_table_type.'_sortable_columns', array($this, 'define_sortable_columns'));
		add_filter('manage_'.$this->post_type.'_posts_columns', array($this, 'define_columns'));
		add_action('manage_'.$this->post_type.'_posts_custom_column', array($this, 'render_columns'), 10, 2);
		
		add_filter('bulk_updated_messages', array($this, 'bulk_messages'), 10, 2);
		add_filter('handle_bulk_actions-'.$this->list_table_type, array($this, 'handle_bulk_actions'), 10, 3);
	}

	/**
	 * (override WP_List_Table)
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 3.1.0
	 * @abstract
	 *
	 * @return array
	 */
	public function get_columns(){
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'profile' => 'Profile',
			'date'  => 'Date',
			'status' => 'Status',
			'export_type' => 'Export Type',
			'autorun' => 'Autorun',
			'actions' => 'Actions'
		);

		return $columns;
	}

	/**
	 * Render individual columns.
	 *
	 * @param string $column Column ID to render.
	 * @param int    $post_id Post ID being shown.
	 */
	public function render_columns($column, $post_id) {
		if (!$this->items) {
			return;
		}
		
		if (is_callable(array($this, 'column_'.$column))) {
			$this->{"column_{$column}"}($this->items);
		}
	}

	/**
	 * Removes this type from list of post types that support "View Mode" switching.
	 * View mode is seen on posts where you can switch between list or excerpt. Our post types don't support
	 * it, so we want to hide the useless UI from the screen options tab.
	 *
	 * @param  array $post_types Array of post types supporting view mode.
	 * @return array             Array of post types supporting view mode, without this type.
	 */
	public function disable_view_mode($post_types) {
		unset($post_types[$this->post_type]);
		return $post_types;
	}

	/**
	 * See if we should render search filters or not.
	 */
	public function restrict_manage_posts() {
		global $typenow;
		
		if ($this->list_table_type === $typenow) {
			$this->render_filters();
		}
	}

	/**
	 * (override WP_List_Table)
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 3.1.0
	 * @abstract
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		
		$sql = 'select id, profile_name as profile, create_date as date, status, autorun, '
			.'filter_value as filter from '.$this->wpdb->prefix.'pf_exporter_profile';

		if (!empty($_REQUEST['s'])) {
			$sql .= ' where profile_name like "%'.esc_attr(wp_unslash($_REQUEST['s'])).'%"';
		}
		$data = $this->wpdb->get_results($sql);
		
		$perPage = 10;
		$currentPage = $this->get_pagenum();
		$totalItems = count($data);

		$this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ));

        $data = array_slice($data, (($currentPage-1)*$perPage), $perPage);

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
	}

	/**
	 * (override WP_List_Table)
	 * Adjust which columns are displayed by default.
	 *
	 * @param array  $hidden Current hidden columns.
	 * @param object $screen Current screen.
	 * @return array
	 */
	public function default_hidden_columns($hidden, $screen) {
		if (isset($screen->id) && $this->list_table_type === $screen->id) {
			$hidden = array_merge($hidden, $this->define_hidden_columns());
		}
		return $hidden;
	}

	/**
	 * (override WP_List_Table)
	 * Define which columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function define_columns($columns) {
		$column = array(
			'cb' => $columns['cb'],// '<input type="checkbox" />',
			'profile' => 'Profile',
			'date'  => 'Date',
			'status' => 'Status',
			'export_type' => 'Export Type',
			'autorun' => 'Autorun',
			'actions' => 'Actions'
		);

		return $column;
	}

	/**
	 * (override WP_List_Table)
	 * Set row actions.
	 *
	 * @param array   $actions Array of actions.
	 * @param WP_Post $post Current post object.
	 * @return array
	 */
	// public function row_actions($actions, $post) {
	// 	if ($this->post_type === $post->post_type) {
	// 		return $this->get_row_actions($actions, $post);
	// 	}
	// 	return $actions;
	// }

	/**
	 * (override WP_List_Table)
	 * Define which columns are sortable.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function define_sortable_columns($columns) {
		$sortable_columns = array(
			'profile' => array('profile', true),
			'date' => array('date', true),
			'export_type' => array('export_type', true)
		);

		return wp_parse_args($sortable_columns, $columns);
	}

	/**
	 * (override WP_List_Table)
	 * Define primary column.
	 *
	 * @return string
	 */
	public function get_primary_column() {
		return 'profile';
	}

	/**
	 * (override WP_List_Table)
	 * Set list table primary column.
	 *
	 * @param  string $default Default value.
	 * @param  string $screen_id Current screen ID.
	 * @return string
	 */
	public function list_table_primary_column($default, $screen_id) {
		if ($this->list_table_type === $screen_id && $this->get_primary_column()) {
			return $this->get_primary_column();
		}
		return $default;
	}

	/**
	 * (override)
	 * Handle bulk actions.
	 *
	 * @param  string $redirect_to URL to redirect to.
	 * @param  string $action      Action name.
	 * @param  array  $ids         List of ids.
	 * @return string
	 */
	public function handle_bulk_actions($redirect_to, $actions, $ids) {
		$count = 0;

		if (count($ids)) {
			switch ($actions) {
				case 'active':
					foreach ($ids as $v) {
						$result = $this->wpdb->update(
							$this->wpdb->prefix."pf_exporter_profile",
							['status' => 1],
							['id' => $v]
						);

						if ($result) {
							$count++;
						}
					}
					break;
				case 'in-active':
					foreach ($ids as $v) {
						$result = $this->wpdb->update(
							$this->wpdb->prefix."pf_exporter_profile",
							['status' => 0],
							['id' => $v]
						);

						if ($result) {
							$count++;
						}
					}
					break;
			}

			$redirect_to = add_query_arg(array('total' => $count, 'action' => $actions), $redirect_to);
		}

		wp_safe_redirect($redirect_to);
	}

	/**
	 * (override WP_List_Table)
	 * Get an associative array (id => link) with the list
	 * of views available on this table.
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	public function get_views() {
		$views = [];
		$active = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"select count(*) as count from {$this->wpdb->prefix}pf_exporter_profile where status=%d",
				1
			)
		);
		$inActive = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"select count(*) as count from {$this->wpdb->prefix}pf_exporter_profile where status=%d",
				0
			)
		);

		$views['all'] = '<a href="edit.php?post_type=exporter_profile" class="current" aria-current="page">All <span class="count">('.($active->count + $inActive->count).')</span></a>';
		$views['active'] = '<a href="'.admin_url("admin.php?page=pfe-profile&status=active").'">Active <span class="count">('.$active->count.')</span></a>';
		$views['not-active'] = '<a href="'.admin_url("admin.php?page=pfe-profile&status=in-active").'">Not active <span class="count">('.$inActive->count.')</span></a>';
		
		return $views;
	}

	/**
	 * (Override WP_List_Table)
	 * Get an associative array (option_name => option_title) with the list
	 * of bulk actions available on this table.
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		$actions = array(
			'active' => 'Active',
			'in-active' => 'Inactive',
			'PF Exporter Profile' => [
				'export' => __('Export', PRESSFERENCE_TEXT_DOMAIN),
				'delete' => __('Delete', PRESSFERENCE_TEXT_DOMAIN)
			]
		);
		return $actions;
	}

	/**
	 * (Override WP_List_Table)
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	public function current_action() {
		if (isset($_POST['action']) && -1 != $_POST['action'])
			return $_POST['action'];

		return false;
	}

	/**
	 * (override WP_List_Table)
	 * If column name not defined will use this column default
	 *
	 * @param object $item 			contain object of data
	 * @param string $column_name   column name
	 */
	public function column_default($item, $column_name) {
	  	switch($column_name) { 
		    case 'profile':
			case 'date':
			case 'status':
			case 'export_type':
			case 'autorun':
		      	return $item->{$column_name};
	    	default:
	    		return print_r($item, true); //Show the whole array for troubleshooting purposes
	  	}
	}

	/**
	 * (override WP_List_Table)
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'profile' => array('profile', true),
			'date' => array('date', true),
			'export_type' => array('export_type', true)
		);

		return $sortable_columns;
	}

	/**
	 * (override WP_List_Table)
	 * Handles the checkbox column output.
	 *
	 * @since 4.3.0
	 *
	 * @param WP_Post $post The current WP_Post object.
	 */
	public function column_cb($item) {
		if (current_user_can('edit_export_profiles', $item->id)) {
			$html = '<label for="cb-select-'.$item->id.'">
				<input id="cb-select-'.$item->id.'" type="checkbox" name="post[]" value="'.$item->id.'" />
			</label>
			<div class="locked-indicator">
				<span class="locked-indicator-icon" aria-hidden="true"></span>
				<span class="screen-reader-text">'.__("&#8220;"._draft_or_post_title()."&#8221; is locked").'
				</span>
			</div>';

			echo wp_kses($html, $this->exporter_handling->get_allowed_html());
		}
	}

	public function column_profile($item) {
		echo $item ? esc_attr($item->profile) : "";
	}

	public function column_date($item) {
		$html = '<time datetime="'.date("Y-m-d H:i:s", strtotime($item->date)).'">'.date("M j, Y", strtotime($item->date)).'</time>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function column_status($item) {
		return $item->status ? "<span>Active</span>" : "<span>Inactive</span>";
	}

	public function column_export_type($item) {
		$availableType = new Pressference_Exporter_Type();
		
		if ($item->filter) {
			$filter = maybe_unserialize($item->filter);
			if ($filter['pf_export_type'] && $availableType->type_check($filter['pf_export_type'])) {
				$typeArr = $availableType->get_type();
				echo $typeArr[esc_attr($filter['pf_export_type'])];
			}
		}
		echo "";
	}

	public function column_autorun($item) {
		echo $item->autorun ? "<span>Yes</span>" : "<span>No</span>";
	}

	public function column_actions($item) {
		$edit = admin_url('admin.php?page=pfe-filter&pid='.$item->id.'&action=edit');
		$delete = admin_url('admin.php?page=pfe-filter&action=delete&pid='.$item->id);

		$html = '<p>'.
			'<a class="button action button-actions tbl-action-ctrl" href="'.$edit.'" aria-label="Edit Profile">'.
			'<i class="far fa-edit"></i>'.
			'</a><a class="button action button-actions tbl-action-ctrl" href="'.$delete.'" aria-label="Delete">'.
			'<i class="far fa-trash-alt"></i>'.
			'</a></p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	protected function get_hidden_columns() {
		return array();
	}

	/**
	 * Post type for this table
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Bulk action message
	 *
	 * @param string $actions type of action being pass
	 * @param integer $count total update item
	 *
	 * @return string
	 */
	public function bulk_messages($action, $count) {
		$msg = '<div class="updated notice is-dismissible">';
		switch ($action) {
			case 'active':
			case 'in-active':
				$msg .= '<p>'.$count.' profile status changed.</p>';
				break;
			case 'export':
				$msg .= '<p>'.$count.' item has been exported.</p>';
				break;
			case 'delete':
				$msg .= '<p>'.$count.' profile has been deleted.</p>';
				break;
		}

		$msg .= '</div>';

		return $count ? $msg : '';
	}

	// Private Method
	private function define_hidden_columns() {
		return array();
	}

	// private function get_row_actions($actions, $post) {
	// 	return $actions;
	// }

	private function getProfileById($id) {
		$order = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"select * from {$this->wpdb->prefix}pf_exporter_profile where id=%d",
				$id
			)
		);

		return $order;
	}
}