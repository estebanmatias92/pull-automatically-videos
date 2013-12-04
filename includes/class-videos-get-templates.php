<?php
/**
 * Get view tenokates.
 *
 * @package    Pull Automatically Videos
 * @subpackage Pull_Automatically_Videos
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    MIT License
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( !class_exists( 'Videos_Get_Templates' ) ) :

    /**
     * Videos_Get_Templates
     *
     * This class will load by default plugin templates for the post-type views, unless the templates are located in the theme folder, in which case it will use these last
     */
    class Videos_Get_Templates extends Pull_Automatically_Videos
    {

        /**
         * Get default or custom template for the custom post-type views.
         *
         * @since 0.1.0
         *
         * @param string    $file_template Default wordpress hierarchy template to use.
         *
         * @return string    If post-type ins't equal to plugin plugin post-type returns default template from wordpress. Else, returns the plugin custom templates.
         */
        public function get_template( $file_template )
        {

            $post_type = get_post_type() != '' ? get_post_type() : get_query_var( 'post_type' );

            if ( $post_type != self::$post_type_select )
                return $file_template;

            if ( is_archive() && ! is_tax() )
                return $this->get_template_hierarchy( 'archive' );

            if ( is_tax() )
                return $this->get_template_hierarchy( 'taxonomy' );

            if ( is_single() )
                return $this->get_template_hierarchy( 'single' );

        }


        /**
         * Search and get the required custom post-type template.
         *
         * @since 0.1.0
         *
         * @param string    $template Custom template required.
         *
         * @return string    If custom template has been located into wordpress theme, return this, else, returns the custom plugin template.
         */
        private function get_template_hierarchy( $template )
        {

            // Get the template slug
            $template_slug      = rtrim( $template, '.php' );
            $template_post_type = $template_slug . '-' . ( $template == 'taxonomy' ? get_query_var( 'taxonomy' ) : self::$post_type_select ) . '.php';
            $template           = $template_slug . '.php';

            // Check if a custom template exists in the theme folder, if not, load the plugin template file
            if ( $theme_file = locate_template( array( $template_post_type ) ) ) {
                $file = $theme_file;
            } else {
                $file = PAV_PLUGIN_ROOT . 'views/templates/' . $template;
            }

            return apply_filters( self::$plugin_slug . '_template_' . $template, $file );

        }

    }

endif;
