<?php

if (!defined('ABSPATH')) {
	exit;
}

if (class_exists('Pressference_Post_Types', false)) {
	return new Pressference_Post_Types();
}

class Pressference_Post_Types {
	public function __construct() {
		$this->register_post_types();
	}

	public function register_post_types() {
		$supports = array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'publicize', 'wpcom-markdown');

		register_post_type('exporter_profile', 
			array(
				'labels' => array(
					'name' => __('Exporter Profiles', PRESSFERENCE_TEXT_DOMAIN),
					'singular_name' => __('Exporter Profile', PRESSFERENCE_TEXT_DOMAIN),
					'all_items' => __('All Profiles', PRESSFERENCE_TEXT_DOMAIN),
					'menu_name' => _x('Profile', 'Admin menu name', PRESSFERENCE_TEXT_DOMAIN),
					'add_new' => __('Add Profile', PRESSFERENCE_TEXT_DOMAIN),
					'add_new_item' => __('Add new profile', PRESSFERENCE_TEXT_DOMAIN),
					'edit' => __('Edit', PRESSFERENCE_TEXT_DOMAIN),
					'edit_item' => __('Edit profile', PRESSFERENCE_TEXT_DOMAIN),
					'new_item' => __('Add Profile', PRESSFERENCE_TEXT_DOMAIN),
					'view_item' => __('View Profile', PRESSFERENCE_TEXT_DOMAIN),
					'view_items' => __('View Profiles', PRESSFERENCE_TEXT_DOMAIN),
					'search_items' => __('Search Profiles', PRESSFERENCE_TEXT_DOMAIN),
					'not_found' => __('No profiles found', PRESSFERENCE_TEXT_DOMAIN),
					'not_found_in_trash' => __('No profiles found in trash', PRESSFERENCE_TEXT_DOMAIN),
					'parent' => __('Parent profile', PRESSFERENCE_TEXT_DOMAIN),
					'featured_image' => __('Profile image', PRESSFERENCE_TEXT_DOMAIN),
					'set_featured_image' => __('Set profile image', PRESSFERENCE_TEXT_DOMAIN),
					'remove_featured_image' => __('Remove profile image', PRESSFERENCE_TEXT_DOMAIN),
					'use_featured_image' => __('Use as profile image', PRESSFERENCE_TEXT_DOMAIN),
					'insert_into_item' => __('Insert into profile', PRESSFERENCE_TEXT_DOMAIN),
					'uploaded_to_this_item' => __('Uploaded to this profile', PRESSFERENCE_TEXT_DOMAIN),
					'filter_items_list' => __('Filter profiles', PRESSFERENCE_TEXT_DOMAIN),
					'items_list_navigation' => __('Profiles navigation', PRESSFERENCE_TEXT_DOMAIN),
					'items_list' => __('Profiles list', PRESSFERENCE_TEXT_DOMAIN),
				),
				'description' => __('saved exporter profile settings.', PRESSFERENCE_TEXT_DOMAIN),
				'public' => true,
				'exclude_from_search' => true,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => false, // current_user_can('manage_pfe') ? 'pfe-main-menu' : true,
				'capability_type' => 'export_profile',
				'map_meta_cap' => true,
				'hierarchical' => false, // Hierarchical causes memory issues - WP loads all records!
				'supports' => $supports,
				'has_archive' => false,
				'rewrite' => false,
				'query_var' => true,
				'can_export' => false
			)
		);
	}
}