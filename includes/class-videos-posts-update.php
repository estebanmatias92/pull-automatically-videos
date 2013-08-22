<?php
/**
 * Update post.
 *
 * @package    Pull Automatically Videos
 * @subpackage Pull_automatically_Videos
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    MIT License
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( ! class_exists( 'Videos_Posts_Update' ) ) :

	/**
	 * This class updates the list of post in wordpress, adds or deletes post, calling other classes and considering the current remote video list.
	 */
	class Videos_Posts_Update extends Pull_automatically_Videos {

		/**
		 * Update the post list with the remote current video list.
		 *
		 * @since  0.1.0
		 *
		 * @return integer   Returns the number of added posts.
		 */
		public function update() {

		    // Check if channel have videos
		    $current_videos = $this->get_all_videos();
		    if ( ! $current_videos ) return 0;

		    // Save new videos & determine list of all current id_list
			$id_list   = array();
			$num_posts = 0;

		    foreach ( $current_videos as $video ) {

		        array_push( $id_list, $video['video_id'] );

		        $add_video = new Videos_Post_Add();

		        if ( $add_video->add( $video ) == true ){
		            $num_posts++;
		        }

		    }

		    // Remove deleted videos
			$remove    = new Videos_Posts_Remove();
			$del_posts = $remove->remove_deleted_posts( $id_list );

		    if ( $del_posts > 0 ) {
		        echo sprintf( _n( 'Note: %d video was deleted on external host and thus removed from this collection.', 'Note: %d videos were deleted on external host and thus removed from this collection.', $del_posts, 'TRADUCIR' ), $del_posts );
		    }

		    return $num_posts;

		}

		/**
		 * Get current remote videos.
		 *
		 * @since  0.1.0
		 *
		 * @return array    Returns current video list.
		 */
		private function get_all_videos(){

		    $current_videos = array();

		    foreach ( self::$authors as $author ) {

		        $new_videos = $this->get_author_videos( $author );

		        array_splice( $current_videos, count( $current_videos ), 0, $new_videos );

		    }

		    return $current_videos;

		}

		/**
		 * Get videos by author.
		 *
		 * @since  0.1.0
		 *
		 * @param  array     		$author       Account ID to get videos.
		 *
		 * @return array/boolean    Returns the videos in array, if not have videos, returns false.
		 */
		private function get_author_videos( $author ) {

	        switch ( $author['host_id'] ) {

	            case 'youtube':
					$fetch  = new Videos_Fetch_Youtube( $author['author_id'] );
					$videos = $fetch->get_videos();
	                break;

                case 'vimeo':
					$fetch  = new Videos_Fetch_Vimeo( $author['author_id'], $author['developer_key'], $author['secret_key'] );
					$videos = $fetch->get_videos();
	                break;

	            default:
	                exit( __( 'Host doesnt exist :(' ) );
	                break;

	        }

	        return isset( $videos ) ? $videos : false;

	    }

	}

endif;
