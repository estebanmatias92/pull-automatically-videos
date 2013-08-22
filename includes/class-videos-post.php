<?php
/**
 * Build the post.
 *
 * @package    Pull Automatically Videos
 * @subpackage Videos_Post_Add
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    MIT License
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( ! class_exists( 'Videos_Post' ) ) :

	/**
	 * This class builds post to insert in wordpress.
	 */
	class Videos_Post extends Videos_Post_Add {

		/**
		 * The post to be built.
		 *
		 * @var array
		 */
		private $post = null;

		/**
		 * ID of the done current post.
		 *
		 * @var string
		 */
		private $post_id = null;

		/**
		 * Build the post.
		 *
		 * @since 0.1.0
		 *
		 * @param array    $video Current video data to post.
		 */
		function __construct( $video ) {

			// Call to post view
			require_once( PAV_PLUGIN_ROOT . 'views/class-videos-post-public.php' );

	    	// Prepare post
			$this->post                   = array();
			$this->post['post_type']      = self::$post_type_select;
			$this->post['post_title']     = $video['title'];
			$this->post['post_content']   = Videos_Post_Public::get_post_content( $video );
			$this->post['post_status']    = $this->get_post_status();
			$this->post['post_author']    = 1;
			$this->post['post_date']      = $video['published'];
			$this->post['tax_input']      = array( self::$taxonomy_select => self::$terms );
			$this->post['post_mime_type'] = 'import';

		    // Save to DB
		    $this->post_id = wp_insert_post( $this->post );

		    // Add post meta
		    $this->set_post_meta( $video );

		    // Sets the given post to the 'video' format
		    set_post_format( $this->post_id, 'video' );

		    // Sets term
		    $this->set_post_terms();

		}

		/**
		 * Sets term of the post by video title.
		 *
		 * @since 0.1.0
		 */
		private function set_post_terms() {

			if ( self::$terms ) {

		        foreach ( self::$terms as $term ) {

		            $str_search = stripos( $this->post['post_title'], $term );

		            // If found in title, some term, sets how post term and break.
		            if ( $str_search !== FALSE ) {
		                wp_set_object_terms( $this->post_id, $term, self::$taxonomy_select );
		                break;
		            }

		        }

		    }

		    /* For get youtube tags, need API authorization so... It's no ridiculous?
		    if ( $terms && ! empty($video['keywords'] ) ) {
		        foreach ($terms as $term){

					// If 'keywords' array, the term, sets how post term and break.
		            if ( in_array( $term, $video['keywords'] ) ) {
		                wp_set_object_terms($post_id, $term, $options['taxonomy_select']);
		                break;
		            }

		        }
		    }*/

		}

		/**
		 * Set and get the status for the post.
		 *
		 * @since  0.1.0
		 *
		 * @return string    Returns the status.
		 */
		private function get_post_status() {

			if ( self::$terms ) {

		        if ( string_have_some_term( $this->post['post_title'], self::$terms ) ) {
		            return self::$post_status_select;
		        } else {
		            return 'pending';
		        }

		    } else {
		        return self::$post_status_select;
		    }

		}

		/**
		 * Sets the post meta.
		 *
		 * @since 0.1.0
		 *
		 * @param array    $video Current video data to post.
		 */
		private function set_post_meta( $video ) {

			add_post_meta( $this->post_id, 'host_id',         $video['host_id'] );
			add_post_meta( $this->post_id, 'author_id',       $video['author_id'] );
			add_post_meta( $this->post_id, 'video_id',        $video['video_id'] );
			add_post_meta( $this->post_id, 'duration',        $video['duration'] );
			add_post_meta( $this->post_id, 'author_url',      $video['author_url'] );
			add_post_meta( $this->post_id, 'video_url',       $video['video_url'] );
			add_post_meta( $this->post_id, 'description',     $video['description'] );

			// Add thumbnail to post
           	update_post_meta( $this->post_id, '_thumbnail_id', $this->upload_attachement( $video['thumbnail_url'] ) );

		}

		/**
		 * This funcion upload an attachment to media library and returns his ID
		 *
		 * @since  0.1.0
		 *
		 * @param  string    $file_url     Hosted url from attachment.
		 *
		 * @return integer   Returns the attachment ID
		 */
	    private function upload_attachement( $file_url ) {

	        require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
	        require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
	        require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );

	        // Upload image to server
	        $result = media_sideload_image( $file_url, $this->post_id, get_the_title( $this->post_id ) );

	        if ( is_wp_error( $result ) ) {
	            return false;
	        }

	        // get the newly uploaded image
	        $attachments = get_posts( array(
	            'post_type'    => 'attachment',
	            'number_posts' => 1,
	            'post_status'  => null,
	            'post_parent'  => $this->post_id,
	            'orderby'      => 'post_date',
	            'order'        => 'DESC'
	        ) );

	        // Returns the id of the image
	        return $attachments[0]->ID;
	    }

	}

endif;
