<?php
/**
 * Remove post class.
 *
 * @package    Pull Automatically Videos
 * @subpackage Pull_Automatically_Videos
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    GPL-2.0+
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( ! class_exists( 'Videos_Posts_Remove' ) ) :

	/**
	 * This class contains all methods to delete post in wordpress in different scenarios.
	 */
	class Videos_Posts_Remove extends Pull_Automatically_Videos {

		/**
		 * All current posts of this plugin.
		 *
		 * @var array
		 */
		private $query_current_post = null;

		/**
		 * Initialize query post, to start to remove posts.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {

			$this->query_current_post = new WP_Query( array(
				'post_type'   => self::$post_type_select,
				'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ),
				'nopaging'    => true
			) );

		}

		/**
		 * Remove all current posts.
		 *
		 * @since  0.1.0
		 *
		 * @return integer   Return the deleted post number.
		 */
		public function remove_all() {

		    $del_posts = 0;

		    while ( $this->query_current_post->have_posts() ) {

		        $this->query_current_post->the_post();

		        $this->remove_attachments( get_the_ID() );
		        wp_delete_post( get_the_ID(), true );

		        $del_posts++;

		    }

		    return $del_posts;

		}

		/**
		 * Remove the author's posts.
		 *
		 * @since  0.1.0
		 *
		 * @param  string    $author_id    Accound ID.
		 * @param  string    $host_id      Host of this account.
		 *
		 * @return integer   Return the deleted post number.
		 */
		public function remove_author_posts( $author_id, $host_id ) {

	        $del_posts = 0;

	        $query_author_post = new wp_Query( array(
				'post_type'  => self::$post_type_select,
				'meta_key'   => 'author_id',
				'meta_value' => $author_id,
				'nopaging'   => true
				) );

	        while ( $query_author_post->have_posts() ){

				$next_post      = $query_author_post->next_post();
				$post_meta_host = get_post_meta( $next_post->ID, 'host_id', true );

	            if ( $post_meta_host == $host_id ) {

	            	$this->remove_attachments( $next_post->ID );
	                wp_delete_post( $next_post->ID, true );

	                $del_posts++;

	            }

	        }

	        return $del_posts;

		}

		/**
		 * Remove deleted post.
		 *
		 * @since  0.1.0
		 *
		 * @param  array     $id_list      Array with all current post meta IDs.
		 *
		 * @return integer   Return the deleted post number.
		 */
		public function remove_deleted_posts( $id_list ) {

			$del_posts = 0;

		    while ( $this->query_current_post->have_posts() ) {

				$next_post    = $this->query_current_post->next_post();
				$post_meta_id = get_post_meta( $next_post->ID, 'video_id', true );

		        if ( ! in_array( $post_meta_id, $id_list ) ) {

		        	$this->remove_attachments( $next_post->ID );
		            wp_delete_post( $next_post->ID, true );

		            $del_posts++;

		        }

		    }

		    return $del_posts;

		}

		/**
		 * This function remove all attachments of the current post
		 *
		 * @since  0.1.0
		 *
		 * @param  integer    $post_id      ID of current post.
		 */
		public function remove_attachments( $post_id ) {

			$attachments = get_children( array(
				'post_parent' => $post_id,
			    'post_type'   => 'attachment',
			    'numberposts' => -1,
			    'post_status' => 'any'
			) );

			foreach ( $attachments as $attach ) {
				wp_delete_attachment( $attach->ID, true );
			}

		}

	}

endif;
