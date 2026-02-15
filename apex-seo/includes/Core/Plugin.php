<?php

declare(strict_types=1);

namespace ApexSEO\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ApexSEO\Admin;
use ApexSEO\Frontend;
use ApexSEO\Sitemap;
use ApexSEO\Schema;
use ApexSEO\Monitor;
use ApexSEO\Images;
use ApexSEO\Rest;

class Plugin {

	private static ?Plugin $instance = null;

	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init();
	}

	private function init(): void {
		// Admin
		if ( is_admin() ) {
			new Admin\MetaBox();
			new Admin\Assets();
			new Admin\Dashboard();
			new Admin\Settings();
		}

		// Frontend
		new Frontend\Head();
		new Schema\Manager();

		// Sitemap (Backend generation but frontend access)
		new Sitemap\Generator();

		// Features
		new Monitor\Links();
		new Images\Auto();

		// REST API
		new Rest\Controller();
	}

	public static function activate(): void {
		Monitor\Links::install();

		// Flush rewrite rules for sitemap
		// We need to instantiate Generator to add rules first
		$generator = new Sitemap\Generator();
		$generator->add_rewrite_rules();
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
