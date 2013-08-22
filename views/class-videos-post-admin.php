<?php
/**
 * Pull Automatically Videos.
 *
 * @package   Pull_Automatically_Videos
 * @author    Matias Esteban <estebanmatias92@gmail.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Matias Esteban
 */

if ( ! class_exists( 'Videos_Post_Admin' ) ) :

    /**
     * This class contains the administration panel view of the posts.
     */
    class Videos_Post_Admin extends Pull_Automatically_Videos {

        /**
         * Instance of this class.
         *
         * @since    0.1.0
         *
         * @var      object
         */
        protected static $instance = null;

        /**
         * Initialize the admin post view.
         *
         * @since 0.1.0
         */
        protected function __construct() {

            add_filter( 'manage_edit-' . self::$post_type_select . '_columns', array( $this, 'columns' ) );

            add_filter( 'manage_edit-' . self::$post_type_select . '_sortable_columns', array( $this, 'sortable_columns' ) );

            add_action( 'load-edit.php', array( $this, 'edit_load' ) );

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
         * Declare custom columns.
         *
         * @since  0.1.0
         *
         * @param  array    $columns      Admin post table columns.
         *
         * @return array    Columns modified.
         */
        public function columns( $columns ) {

            $new = array();
            foreach( $columns as $key => $title ) {

                // Put the Thumbnail column before the Author column
                if ( $key == 'title' ) {
                    $new['_thumbnail_id'] = '';
                }

                // Put comments before the date
                if ( $key == 'date' ) {
                    $new['comments'] = '<div title="comments" class="comment-grey-bubble"></div>';
                }

                $new[$key] = $title;

            }

            return $new;

        }

        /**
         * Make a column sortable.
         *
         * @since  0.1.0
         *
         * @param  object    $columns      Admin post table object.
         *
         * @return object    Returns the object.
         */
        function sortable_columns( $columns ) {

            $columns['duration'] = 'duration';

            return $columns;

        }

        /**
         * Apply sort columns.
         *
         * @since  0.1.0
         *
         * @return null   Apply this filter when the wp hook call this function.
         */
        public function edit_load() {
            add_filter( 'request', array( $this, 'sort_duration_column' ) );
        }

        /**
         * Make a sortable column functionality.
         *
         * @since  0.1.0
         *
         * @param  object    $vars      Admin post table object.
         *
         * @return object    Returns sortable column when the hook call him.
         */
        public function sort_duration_column( $vars ) {

            // Check if we're viewing the self::$post_type_select post type.
            if ( isset( $vars['post_type'] ) && self::$post_type_select == $vars['post_type'] ) {

                // Check if 'orderby' is set to 'duration'.
                if ( isset( $vars['orderby'] ) && 'duration' == $vars['orderby'] ) {

                    // Merge the query vars with our custom variables.
                    $vars = array_merge(
                        $vars,
                        array(
                            'meta_key' => 'duration',
                            'orderby'  => 'meta_value_num'
                            )
                        );

                }

            }

            return $vars;

        }

    }

endif;
