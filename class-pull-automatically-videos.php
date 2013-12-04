<?php
/**
 * Pull Automatically Videos.
 *
 * @package   Pull_Automatically_Videos
 * @author    Matias Esteban <estebanmatias92@gmail.com>
 * @license   MIT License
 * @link      http://example.com
 * @copyright 2013 Matias Esteban
 */

// Includes
require_once( 'includes/helpers.php' );
require_once( 'includes/class-videos-check-author.php' );
require_once( 'includes/class-videos-posts-update.php' );
require_once( 'includes/class-videos-posts-remove.php' );
require_once( 'includes/class-videos-post-add.php' );
require_once( 'includes/class-videos-post.php' );
require_once( 'includes/class-videos-fetch-youtube.php' );
require_once( 'includes/class-videos-fetch-vimeo.php' );
require_once( 'includes/class-videos-get-templates.php' );

/**
 * Pull_Automatically_Videos.
 *
 * Plugin class, pull videos from YouTube, Vimeo & more sites (soon), automatically, and post these in Wordpress.
 *
 * @package Pull_Automatically_Videos
 * @author  Matias Esteban <estebanmatias92@gmail.com>
 */
class Pull_Automatically_Videos {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	protected $version = '0.1.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected static $plugin_slug = 'pull_automatically_videos';

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Plugin default post type and tax values.
	 */
	protected static $default_post_type     = 'galeria-videos';

	protected static $default_type_singular = 'Video';

	protected static $default_type_name     = 'Galería videos';

	protected static $default_taxonomy      = 'categoria-video';

	protected static $default_tax_singular  = 'Categoría video';

	protected static $default_tax_name      = 'Categoría videos';

	/**
	 * Current taxonomy terms array;
	 *
	 * @var [type]
	 */
	protected static $terms = null;

	/**
	 * Author accounts list.
	 *
	 * @var array
	 */
	protected static $authors = array();

	/**
	 * Author hosts list.
	 *
	 * @var array
	 */
	protected static $hosts = array(
		'youtube' => 'YouTube',
		'vimeo'   => 'Vimeo'
		);

	/**
	 * Variable to the Rss option on settings.
	 *
	 * @var boolean
	 */
	protected static $rss = false;

	/**
	 * Variable to the Upload condition option on settings .
	 *
	 * @var boolean
	 */
	protected static $upload_condition = false;

	/**
	 * Variable to the Interval cron option on settings.
	 *
	 * @var string
	 */
	protected static $fetch_intervals = 'hourly';

	/**
	 * Variable to the Post type selected option on settings.
	 *
	 * @var string
	 */
	protected static $post_type_select   = '';

	/**
	 * Variable to the Taxonomy selected option on settings.
	 *
	 * @var string
	 */
	protected static $taxonomy_select    = '';

	/**
	 * Variable to the Post status selected option on settings.
	 *
	 * @var string
	 */
	protected static $post_status_select = 'publish';

	/**
	 * Name for the post type page and menu item.
	 *
	 * @var string
	 */
	protected static $page_name = '';


	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     0.1.0
	 */
	protected function __construct() {

		// Create post type and taxonomies
		add_action( 'after_setup_theme', array( $this, 'create_post_type_and_tax' ) );

		// Update values
		self::$page_name = self::$default_type_name;
		add_action( 'wp_loaded', array( $this, 'set_properties' ) );

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Set post custom post columns in admin view
		add_action( 'admin_init', array( $this, 'admin_columns' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Set interval function
		add_action( self::$plugin_slug .'_event', array( $this, 'interval_triggers' ) );

		// Add cron intervals
		add_filter( 'cron_schedules', array( $this, 'add_intervals' ) );

		// Set feed rss
		add_filter( 'request', array( $this, 'feed_request' ) );

		// Get the post type templates
		add_filter( 'template_include', array( $this, 'template_chooser' ), 10, 2 );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    0.1.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		// Add option to database (if this options doesn't exist).
		// Fix this.
		/*if ( ! get_option( self::$plugin_slug . '_option' ) ) {
			add_option( self::$plugin_slug . '_option', '255', '', 'yes' );
		}*/

	    // Start the cron
	    if ( ! wp_next_scheduled( self::$plugin_slug .'_event' ) ) {
	        wp_schedule_event( time(), self::$fetch_intervals, self::$plugin_slug .'_event' );
	    }

	    // Insert the page (views)
	    add_page_and_menu( self::$page_name, PAV_PLUGIN_ROOT . 'views/templates/archive.php' );

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    0.1.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

	    // Unregister cron
	    wp_clear_scheduled_hook( self::$plugin_slug .'_event' );

	    // Delete archive page
	    remove_page_and_menu( self::$page_name, true );

	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @since  0.1.0
	 */
	public static function uninstall() {

		// Delete archive page and menu
	    remove_page_and_menu( self::$page_name, true );

		// Delete all post-type terms, and taxonomies
		add_action( 'unregister_post_type', 'delete_post_type_taxonomies', 10 );

		// Delete all related posts and his attachments
		delete_post_type_posts( self::$post_type, true, true );

		// Remove post-type from Wordpress
		unregister_post_type( self::$post_type );

		// Delete plugin option
		delete_option( self::$plugin_slug . '_option' );

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		$domain = self::$plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     0.1.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		global $post_type;

		if ( self::$post_type_select == $post_type ) {
			wp_enqueue_style( self::$plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     0.1.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( self::$plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		}

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( self::$plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( self::$plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    0.1.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_plugins_page(
			__( 'Pull Automatically Videos', self::$plugin_slug ),
			__( 'Pull Automatically Videos', self::$plugin_slug ),
			'manage_options',
			self::$plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.1.0
	 */
	public function display_plugin_admin_page() {

		include_once( 'views/admin.php' );

	}

	/**
	 * Add admin post view.
	 *
	 * @since  0.1.0
	 */
	public function admin_columns() {

		require_once('views/class-videos-post-admin.php');

		Videos_Post_Admin::get_instance();

	}

	/**
	 * Function to create the plugin post type and taxonomy.
	 *
	 * @since  0.1.0
	 *
	 * @return null      The post type and tax registered when the call him.
	 */
	public function create_post_type_and_tax() {

		if ( ! class_exists( 'Super_Custom_Post_Type' ) )
			return;

		// Add post type
		$post_type_labels =  array(
			'supports' => array( 'title', 'editor', 'thumbnail', 'post-formats' ),
    		);
	    $post_type = new Super_Custom_Post_Type( self::$default_post_type, self::$default_type_singular, self::$default_type_name, $post_type_labels );

	    // Add meta to admin columns
		$post_type->add_to_columns( array(
			'_thumbnail_id' => '',
			//'host_id'		=> __( 'Host' ),
			'duration'		=> __( 'Duration' )
		) );

	    // Add taxonomy
		$taxonomy_labels = array(
			'show_admin_column' => true,
		);
	    $taxonomy = new Super_Custom_Taxonomy( self::$default_taxonomy, self::$default_tax_singular, self::$default_tax_name, 'cat', $taxonomy_labels );

	    // Connect post type and taxes
		connect_types_and_taxes( $post_type, array( $taxonomy ) );

	}

	/**
	 * Get properties values.
	 *
	 * @since  0.1.0
	 *
	 * @return array     Returns properties values, to start the object.
	 */
	private function get_properties() {

		return array(
			'authors'            => self::$authors,
			'rss'                => self::$rss,
			'upload_condition'   => self::$upload_condition,
			'fetch_intervals'    => self::$fetch_intervals,
			'post_type_select'   => self::$post_type_select,
			'taxonomy_select'    => self::$taxonomy_select,
			'post_status_select' => self::$post_status_select
			);

	}

	/**
	 * Set properties values
	 *
	 * @since 0.1.0
	 */
	public function set_properties() {

		if ( empty( self::$post_type_select ) ) {
			self::$post_type_select = self::$default_post_type;
		}

		if ( empty( self::$taxonomy_select ) ) {
			self::$taxonomy_select = self::$default_taxonomy;
		}

    	$options = get_option( self::$plugin_slug . '_option', $this->get_properties() );

		self::$authors            = $options['authors'];
		self::$rss                = $options['rss'];
		self::$upload_condition   = $options['upload_condition'];
		self::$fetch_intervals    = $options['fetch_intervals'];
		self::$post_type_select   = $options['post_type_select'];
		self::$taxonomy_select    = $options['taxonomy_select'];
		self::$post_status_select = $options['post_status_select'];

		self::$terms              = get_terms( self::$taxonomy_select, array( 'fields' => 'names', 'hide_empty' => false ) );

	}

	/**
	 * Get update values, and set plugin properties.
	 *
	 * @since  0.1.0
	 *
	 * @param  boolean    $rss                Update value.
	 * @param  boolean    $upload_condition   Update value.
	 * @param  string     $fetch_intervals    Update value.
	 * @param  string     $post_type_select   Update value.
	 * @param  string     $taxonomy_select    Update value.
	 * @param  string     $post_status_select Update value.
	 */
	public function update_properties( $rss, $upload_condition, $fetch_intervals, $post_type_select, $taxonomy_select, $post_status_select ) {

		self::$rss = $rss == 'rss' ? true : false;

		self::$upload_condition = $upload_condition == 'upload_condition' && self::$terms ? true : false;

        if ( isset( $fetch_intervals ) ) {
            self::$fetch_intervals = $fetch_intervals;
        }

        if ( isset( $post_type_select ) ) {
            self::$post_type_select = $post_type_select;
        }

        if ( isset( $taxonomy_select ) ) {
            self::$taxonomy_select = $taxonomy_select;
        }

        if ( isset( $post_status_select ) ) {
            self::$post_status_select = $post_status_select;
        }

	}

	/**
	 * Fired with the plugin cron interval.
	 *
	 * @since  0.1.0
	 */
	public function interval_triggers() {

		$update = new Videos_Posts_Update();
		$var    = $update->update();

	}

	/**
	 * [add_intervals description]
	 *
	 * @since 	0.1.0
	 *
	 * @param 	array     $schedules Cron array values to add news.
	 *
	 * @return 	array	  The cron array with new values.
	 */
	public function add_intervals( $schedules ){

	    $schedules['ten-minutes'] = array(
			'interval' => 600,
			'display'  => __( 'Every Ten Minutes' )
	    );

	    $schedules['half-hour'] = array(
			'interval' => 1800,
			'display'  => __( 'Every Half Hour' )
	    );

	    $schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly' )
	    );

	    $schedules['monthly'] = array(
			'interval' => 2635200,
			'display'  => __( 'Once a month' )
	    );

	    return $schedules;

	}

	/**
	 * Function to add to the feed rss, the plugin pos type.
	 *
	 * @since  0.1.0
	 *
	 * @param  object    $query_var    Current query object.
	 *
	 * @return object    Returns the post type values modified.
	 */
	public function feed_request( $query_var ) {

	    if ( self::$rss == true ) {

	        if ( isset( $query_var['feed'] ) && ! isset( $query_var['post_type'] ) ) {
	            $query_var['post_type'] = array( self::$post_type_select, 'post' );
	        }

	    }

	    return $query_var;

	}

	/**
	 * Get the custom post-type templates.
	 *
	 * @since 0.1.0
	 *
	 * @param string    $template Default wordpress hierarchy template to use.
	 *
	 * @return string    The current template file that will be use.
	 */
	public function template_chooser( $template ) {

		$file = new Videos_Get_Templates();

		return $file->get_template( $template );

	}

}
