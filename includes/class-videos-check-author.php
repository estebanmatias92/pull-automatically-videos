<?php
/**
 * Check the author imput.
 *
 * @package    Pull Automatically Videos
 * @subpackage Pull_Automatically_Videos
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    GPL-2.0+
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( ! class_exists( 'Videos_Check_Author' ) ) :

	/**
	 * This class input authors verifies. Also deletes authors.
	 */
	class Videos_Check_Author extends Pull_Automatically_Videos {

		/**
		 * Current Author entered.
		 *
		 * @var string
		 */
		private $author_id = null;

		/**
		 * Current Host entered.
		 *
		 * @var string
		 */
		private $host_id = null;

		/**
		 * Key from the current count.
		 *
		 * @var string
		 */
		private $developer_key = null;

		/**
		 * Key from the current count.
		 *
		 * @var string
		 */
		private $secret_key = null;

		/**
		 * Gets the account data to check.
		 *
		 * @since 0.1.0
		 *
		 * @param string    $host_id   Host newly entered ready to review.
		 * @param string    $author_id Author newly entered ready to review.
		 */
		function __construct( $host_id, $author_id, $developer_key = '', $secret_key = '' ) {

			$this->author_id     = $author_id;
			$this->host_id       = $host_id;
			$this->developer_key = $developer_key;
			$this->secret_key    = $secret_key;

		}

		/**
		 * Check if the current author and the host exist.
		 *
		 * @since  0.1.0
		 *
		 * @return boolean   If can find the author-host, returns true.
		 */
		public function remote_author_exists(){

			if ( empty( $this->author_id ) ) {
				return false;
			}

		    $url = null;

		    switch ( $this->host_id ) {

		    	case 'youtube':
		            $url = "http://www.youtube.com/$this->author_id";
		        	break;

		        case 'vimeo':
		            $url = "http://www.vimeo.com/$this->author_id";
		        	break;

		        default:
	                exit( __( 'Host doesnt exist :(' ) );
	                break;

		    }

		    $headers = wp_remote_request( $url );

		    if ( is_wp_error( $headers ) ) {
		        // Return false on error
		        return false;
		    }


		    if ( ! $headers || preg_match( '/^[45]/', $headers['response']['code'] ) ) {
		        return false;
		    }

		    return true;

		}

		/**
		 * Checks if the current author agrees with the saved authors.
		 *
		 * @since  0.1.0
		 *
		 * @return boolean   If can't find the author-host, returns false.
		 */
		public function local_author_exists() {

		    foreach ( self::$authors as $author ) {

		        if ( $author['author_id'] == $this->author_id && $author['host_id'] == $this->host_id ) {
		            return true;
		        }

		    }

		    return false;

		}

		/**
		 * Checks if developer_key & secret_key are empty.
		 *
		 * @since  0.1.0
		 *
		 * @param  string    $developer_key Key of current account.
		 * @param  string    $secret_key    Key of current account.
		 *
		 * @return boolean   If key are not empty, return true.
		 */
		public function authorization_exists() {

		    switch ( $this->host_id ) {

		        case 'youtube':
		            return true;
		        	break;

		        case 'vimeo':
		            if ( $this->developer_key == '' || $this->secret_key == '' ) {
		                return false;
		            }
        			break;

        		default:
        			return false;
        			break;

		    }

		    return true;

		}

		/**
		 * Checks if the current host agree with the saved hosts.
		 *
		 * @since  0.1.0
		 *
		 * @return boolean   If host exist, returns true.
		 */
		public function host_exists() {

			if ( ! array_key_exists( $this->host_id, self::$hosts ) ) {
				return false;
			}

			return true;

		}

		/**
		 * When you delete an author, this function removes it from the list of saved authors.
		 *
		 * @since  0.1.0
		 *
		 * @return integer   Number of removed author posts.
		 */
		public function delete_author() {

            foreach ( self::$authors as $key => $author ) {

                if ( $author['host_id'] == $this->host_id && $author['author_id'] == $this->author_id ) {
                    unset( self::$authors[$key] );
                }

            }

            // Update the authors array
            self::$authors = array_values( self::$authors );

            // Remove the author's posts
			$remove    = new Videos_Posts_Remove();
			$del_posts = $remove->remove_author_posts( $this->author_id, $this->host_id );

            return $del_posts;

		}

	}

endif;
