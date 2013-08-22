<?php
/**
 * Class to the posts view.
 *
 * @package    Pull Automatically Videos
 * @subpackage Pull_Automatically_Videos
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    MIT License
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( ! class_exists( 'Videos_Post_Public' ) ) :

	/**
	 * This class contains the post view to build.
	 */
	class Videos_Post_Public {

		/**
		 * The video data.
		 *
		 * @var array
		 */
		private static $video = null;

		/**
		 * Build the view for the post.
		 *
		 * @since  0.1.0
		 *
		 * @param  array     $video        The video data.
		 *
		 * @return array     Returns the content with html markup.
		 */
		public static function get_post_content( $video ) {

			self::$video = $video;

			$content = null;

			// Put content together
			$content .= "\n";
		    $content .= self::$video['video_url'];
		    $content .= "\n\n";
		    $content .= '<p>'.self::$video['description'].'</p>';

		    return $content;

		}

	}

endif;
