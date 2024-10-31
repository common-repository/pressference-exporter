<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Exporter_Activator {
	public static function activate(){
		if (class_exists('Pressference_Exporter_Install', false)) {
			return new Pressference_Exporter_Install();
		}
	}
}