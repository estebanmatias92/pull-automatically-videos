<?php
/**
 * Fetch vimeo videos.
 *
 * @package    Pull Automatically Videos
 * @subpackage phpVimeo
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    GPL-2.0+
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

// Includes
require_once( plugin_dir_path( __FILE__ ) . 'vimeo/phpVimeo.php' );

if ( ! class_exists( 'Videos_Fetch_Vimeo' ) ) :

	/**
	 * This class pulls the videos from Vimeo through Vimeo api.
	 */
	class Videos_Fetch_Vimeo extends phpVimeo {

		/**
		 * ID of Vimeo account.
		 *
		 * @var string
		 */
		private $author_id = null;

		/**
		 * Gets the Vimeo account, developer key and secret key to bring the data.
		 *
		 * @since 0.1.0
		 *
		 * @param string    $author_id     ID of Vimeo account.
		 * @param string    $developer_key Key of this account.
		 * @param string    $secret_key    Key of this account.
		 */
		function __construct( $author_id, $developer_key, $secret_key ) {

			$this->author_id        = $author_id;

			// Set the phpVimeo properties values
			$this->_consumer_key    = $developer_key;
			$this->_consumer_secret = $secret_key;

		}

		/**
		 * Brings the data of the account from the Vimeo API.
		 *
		 * @since  0.1.0
		 *
		 * @return array    The video data in an array.
		 */
		function get_videos() {

			$date     = date( DATE_RSS );
			$page     = 1;
			$per_page = 50;
			$videos   = array();

		    // Loop through all feed pages
		    do {

		        // Do an authenticated call
		        try {

		            $videofeed = $this->call(
		            	'vimeo.videos.getUpLoaded',
						array(
							'user_id'       => $this->author_id,
							'full_response' => 'true',
							'page'          => $page,
							'per_page'      => $per_page
						    ),
						'GET',
						parent::API_REST_URL,
						false,
						true
						);

		        }
		        catch ( VimeoAPIException $e ) {
		            echo "Encountered an API error -- code {$e->getCode()} - {$e->getMessage()}";
		        }

		        foreach ( $videofeed->videos->video as $item ) {

		            // Extract fields
					$video                = array();
					$video['host_id']     = 'vimeo';
					$video['author_id']   = strtolower( $this->author_id );
					$video['video_id']    = $item->id;
					$video['title']       = $item->title;
					$video['description'] = $item->description;
					$video['author_name'] = $item->owner->display_name;
					$video['video_url']   = $item->urls->url[0]->_content;
					$video['published']   = $item->upload_date;
					$video['author_url']  = "https://www.vimeo.com/".$video['author_id'];
					$video['category']    = array(); // WORK !!!!!!!!
					$video['keywords']    = array(); // WORK !!!!!!!!

		            if ( $item->tags ) {

		                foreach ( $item->tags->tag as $tag ) {
		                    array_push( $video['keywords'], $tag->_content );
		                }

		            }

					$video['thumbnail_url'] = $item->thumbnails->thumbnail[0]->_content;
					$video['duration']      = seconds_to_hms( $item->duration );

		            // Add $video to the end of $videos
		            array_push( $videos, $video );

		        }

		        // Next page
		        $page += 1;
		    } while ( $videofeed->videos->on_this_page == $per_page );

		    return $videos;

		}

	}

endif;
