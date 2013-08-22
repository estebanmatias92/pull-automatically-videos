<?php
/**
 * Fetch youtube videos.
 *
 * @package    Pull Automatically Videos
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    MIT License
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( ! class_exists( 'Videos_Fetch_Youtube' ) ) :

	/**
	 * This class pulls the videos from YouTube through YouTube api.
	 */
	class Videos_Fetch_Youtube {

		/**
		 * ID of YouTube account.
		 *
		 * @var string
		 */
		private $author_id = null;

		/**
		 * Gets the YouTube account to bring the data.
		 *
		 * @since 0.1.0
		 *
		 * @param string    $author_id ID of YouTube account.
		 */
		public function __construct( $author_id ) {

			$this->author_id = $author_id;

		}

		/**
		 * Brings the data of the account from the YouTube API.
		 *
		 * @since  0.1.0
		 *
		 * @return array    The video data in an array.
		 */
		public function get_videos() {

			$uri    = "http://gdata.youtube.com/feeds/api/users/$this->author_id/uploads/";
			$date   = date( DATE_RSS );
			$videos = array();

		    // Loop through all feed pages
		    while ( $uri != NULL ) {

				$videofeed = fetch_feed( $uri );
				$length    = $videofeed->get_item_quantity();

		        if ( $length != 0 ) {

		            $items = $videofeed->get_items( 0,$length );

		            for ( $i = 0; $i < $length; $i++ ) {

		                // media:group mediaRSS subpart
						$mediagroup = $items[$i]->get_enclosure();

						// Extract fields
						$video                = array();
						$video['host_id']     = 'youtube';
						$video['author_id']   = strtolower( $this->author_id );
						$video['video_id']    = preg_replace('/https:\/\/gdata.youtube.com\/feeds\/api\/videos\//', '', $items[$i]->get_id());
						$video['title']       = $items[$i]->get_title();
						$video['description'] = $items[$i]->get_content();
						$video['author_name'] = $items[$i]->get_author()->get_name();
						$video['video_url']   = preg_replace('/\&amp;feature=youtube_gdata/','', $items[$i]->get_link());
						$video['published']   = date( "Y-m-d H:i:s", strtotime( $items[$i]->get_date() ) );
						$video['author_url']  = "https://www.youtube.com/user/".$video['author_id'];

		                if ( $mediagroup != NULL ) {
		                    $video['category']      = $mediagroup->get_category()->get_label(); // WORK !!!!!!!!
		                    $video['keywords']      = $mediagroup->get_keywords(); // WORK !!!!!!!!
		                    $video['thumbnail_url'] = $mediagroup->get_thumbnail();
		                    $video['duration']      = $mediagroup->get_duration( $convert = true );
		                }

		                // Add $video to the end of $videos
		                array_push( $videos, $video );

		            }

		        }

		        // Next feed page, if available
				$next_url = $videofeed->get_links( $rel = 'next' );
				$uri      = $next_url[0];

		    }

		    return $videos;

		}

	}

endif;
