<?php
/**
 * Add post.
 *
 * @package    Pull Automatically Videos
 * @subpackage Pull_Automatically_Videos
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    MIT License
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( ! class_exists( 'Videos_Post_Add' ) ) :

	/**
	 * This class adds a post in wordpress, to build the post calls another class.
	 */
	class Videos_Post_Add extends Pull_Automatically_Videos {

		/**
		 * Add a post with current entered video data.
		 *
		 * @since  0.1.0
		 *
		 * @param  array     $video Current video data to post.
		 *
		 * @return boolean	 If can post correctly the video, returns true
		 */
		public function add( $video ) {

		    $query = new WP_Query( array(
				'post_type'   => self::$post_type_select,
				'post_status' => array('publish', 'pending', 'draft', 'future', 'private'),
				'meta_key'    => 'video_id',
				'meta_value'  => $video['video_id']
				) );

		    // See if video exists
		    while( $query->have_posts() ) {

				$next_query   = $query->next_post();
				$post_meta_id = get_post_meta( $next_query->ID, 'video_id', true );

		        if ( get_post_meta( $next_query->ID, 'host_id', true ) || $post_meta_id == $video['video_id'] ) {
		            return false;
		        }

		    }

		    // Create post
		    if ( self::$upload_condition == true ) {

		        if ( string_have_some_term( $video['title'], self::$terms ) ) {
					new Videos_Post( $video );
		        }
		        else {
		            return false;
		        }

		    }
		    else{
				new Videos_Post( $video );
		    }

		    return true;

		}
	}

endif;
