<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Pull Automatically Videos
 * @author    Matias Esteban <estebanmatias92@gmail.com>
 * @license   MIT License
 * @link      http://example.com
 * @copyright 2013 Matias Esteban
 */
?>

<form id="delete_author" method="post" action="<?php echo $_SERVER["REQUEST_URI"] ?>" style="display: none">
    <input type="hidden" name="plugin-video" value="ACTIVE">
    <input type="hidden" name="action" value="delete_author">
    <input type="hidden" name="_host_id">
    <input type="hidden" name="_author_id">
</form>

<script>
    function delete_author(host_id, author_id) {
      jQuery('#delete_author [name="_host_id"]').val(host_id);
      jQuery('#delete_author [name="_author_id"]').val(author_id);
      var confirmtext = <?php echo '"'. sprintf(__('Are you sure you want to remove %s on %s?', 'TRADUCIR'), '"+ author_id +"', '"+ host_id +"') .'"'; ?>;
      if (!confirm(confirmtext)) {
          return false;
      }
      jQuery('#delete_author').submit();
    }
</script>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php

	// Clean date white spaces
	$_POST['_author_id']     = isset( $_POST['_author_id'] ) ? trim( $_POST['_author_id'] ) : '';
	$_POST['_secret_key']    = isset( $_POST['_secret_key'] ) ? trim( $_POST['_secret_key'] ) : '';
	$_POST['_developer_key'] = isset( $_POST['_developer_key'] ) ? trim( $_POST['_developer_key'] ) : '';

	// Update option array
	if ( isset( $_POST['plugin-video'] ) && $_POST['plugin-video'] == 'ACTIVE' ) {

	    // Messages and functions of ADD_AUTHOR action
	    if ( $_POST['action'] == 'add_author' ) {

	    	// Check author data, and log errors
	    	$check = new Videos_Check_Author( $_POST['_host_id'], $_POST['_author_id'], $_POST['_developer_key'], $_POST['_secret_key'] );

	        if ( ! $check->host_exists() ) {

	            ?><div class="error"><p><?php echo __( 'Invalid video host.', 'TRADUCIR' ); ?></p></div><?php

	        } elseif ( $check->local_author_exists() ) {

	            ?><div class="error"><p><?php echo __( 'Author already exists.', 'TRADUCIR' ); ?></p></div><?php

	        } elseif ( ! $check->remote_author_exists() ) {

	            ?><div class="error"><p><?php echo __( 'Invalid author.', 'TRADUCIR' ); ?></p></div><?php

	        } elseif ( ! $check->authorization_exists() ) {

	            ?><div class="error"><p><?php echo __( 'Missing developer key.', 'TRADUCIR' ); ?></p></div><?php

	        } else {

	        	// If all it's ok, add the new author entry in the array
	            self::$authors[] = array(
					'host_id'       => $_POST['_host_id'],
					'author_id'     => $_POST['_author_id'],
					'developer_key' => $_POST['_developer_key'],
					'secret_key'    => $_POST['_secret_key']
					);

	            // Save data, & log message
	        	update_option( self::$plugin_slug . '_option', $this->get_properties() );
	            ?>
	            <div class="updated"><p><?php echo __( 'Added author.', 'TRADUCIR' ); ?></p></div><?php

	        }

	    } elseif ( $_POST['action'] == 'delete_author' ) {

	    	// Check author data, and log errors
	    	$check = new Videos_Check_Author( $_POST['_host_id'], $_POST['_author_id'] );

	        if ( ! $check->local_author_exists() ) {

	            ?><div class="error"><p><?php echo __( "Can't delete an author that doesn't exist.", 'TRADUCIR' ); ?></p></div><?php

	        } else {

	        	// Delete the author and his videos
	        	$del_posts = $check->delete_author();

	            // Save data, & log message
	        	update_option( self::$plugin_slug . '_option', $this->get_properties() );
	            ?>
	            <div class="updated"><p><?php printf( _n( 'Deleted author and his %d video.', 'Deleted author and his %d videos.', $del_posts, 'TRADUCIR' ), $del_posts ); ?></p></div><?php

	        }

	    } elseif ( $_POST['action'] == 'update' ) {

	    	// Maney update videos
			$update    = new Videos_Posts_Update();
			$num_posts = $update->update();

			// Save data, & log message
	        update_option( self::$plugin_slug . '_option', $this->get_properties() );
	        ?>
	        <div class="updated"><p><?php printf( _n( 'Found and post %d video.', 'Found and post %d videos.', $num_posts, 'TRADUCIR' ), $num_posts ); ?></p></div><?php

	    } elseif ( $_POST['action'] == 'delete_all' ) {

	    	// Delete all videos
			$delete    = new Videos_Posts_Remove();
			$del_posts = $delete->remove_all();

			// Save data, & log message
	        update_option( self::$plugin_slug . '_option', $this->get_properties() );
	        ?>
	        <div class="updated"><p><?php printf( _n( 'Deleted %d video.', 'Deleted %d videos.', $del_posts, 'TRADUCIR' ), $del_posts ); ?></p></div><?php

	    } elseif ( $_POST['action'] == 'videos_settings' ) {

	    	// Messages of VIDEOS_SETTINGS action
	        ?><div class="updated"><p><strong><?php

	        // Rss & Upload option option define
			$_POST['_rss']              = isset( $_POST['_rss'] ) ? 'rss' : '';
			$_POST['_upload_condition'] = isset( $_POST['_upload_condition'] ) ? 'upload_condition' : '' ;

			// Save settings in class properties
			$this->update_properties(
				$_POST['_rss'],
				$_POST['_upload_condition'],
				$_POST['_fetch_intervals'],
				$_POST['_post_type_select'],
				$_POST['_taxonomy_select'],
				$_POST['_post_status_select']
				);

	        // Save data, & log message
	        update_option( self::$plugin_slug . '_option', $this->get_properties() );
	        _e( 'Saved settings.', 'TRADUCIR' );

	        ?></strong></p></div><?php

	    }
	}
?>

	<?php // Display inputs plugin interface ?>
	<h3><?php _e( 'Add authors', 'TRADUCIR' ); ?></h3>
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	    <input type="hidden" name="plugin-video" value="ACTIVE">
	    <input type="hidden" name="action" value="add_author">
	    <table class="form-table">
	        <tbody>
	            <tr valign="top">
	                <th scope="row">
	                    <label for="_author_id"><?php _e( 'Author ID', 'TRADUCIR' ); ?></label>
	                </th>
	                <td>
	                    <input type="text" name="_author_id" id="_author_id" class="regular-text" value="">
	                </td>
	            </tr>

	            <tr valign="top">
	                <th scope="row">
	                    <label for="_developer_key"><?php _e( 'Developer key', 'TRADUCIR' ); ?></label>
	                </th>
	                <td>
	                    <input type="text" name="_developer_key" id="_developer_key" class="regular-text" value="">

	                    <p class="description">
	                        <?php _e( '(required for Vimeo, leave empty otherwise)', 'TRADUCIR' ); ?>
	                        <a href="http://vimeo.com/api/docs/getting-started"><?php _e( 'See more here', 'TRADUCIR' ); ?></a>
	                    </p>
	                </td>
	            </tr>

	            <tr valign="top">
	                <th scope="row">
	                    <label for="_secret_key"><?php _e( 'Secret key', 'TRADUCIR' ); ?></label>
	                </th>
	                <td>
	                    <input type="text" name="_secret_key" id="_secret_key" class="regular-text" value="">

	                    <p class="description">
	                        <?php _e( '(required for Vimeo, leave empty otherwise)', 'TRADUCIR' ); ?>
	                    </p>
	                </td>
	            </tr>

	            <tr valign="top">
	                <th scope="row">
	                    <label for="_host_id"><?php _e( 'Video host', 'TRADUCIR' ); ?></label>
	                </th>
	                <td>
	                    <select name="_host_id" id="_host_id">
	                        <?php
	                        foreach ( self::$hosts as $key => $value ) {
	                            echo "<option value=\"$key\">" .$value. "</option>";
	                        }
	                        ?>
	                    </select>
	                </td>
	            </tr>
	        </tbody>
	    </table>

	    <p class="submit">
	        <input type="submit" name="Submit" class="button" value="<?php _e( 'Add new author', 'TRADUCIR' ); ?>">
	    </p>
	</form>

	<h3><?php _e( 'Add/Update & delete videos', 'TRADUCIR' ); ?></h3>
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	    <input type="hidden" name="plugin-video" value="ACTIVE">
	    <input type="hidden" name="action" value="update">

	    <table class="form-table">
	        <tbody>
	            <tr valign="top">
	                <th scope="row">
				        <input type="submit" name="Submit" class="button" value="<?php _e( 'Update video list', 'TRADUCIR' ); ?>">
	                </th>
	                <td>
	                    <p>
					        <?php _e( 'Newly added/deleted videos', 'TRADUCIR' ); ?>
					    </p>
	                </td>
	            </tr>
	        </tbody>
	    </table>
	</form>

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	    <input type="hidden" name="plugin-video" value="ACTIVE">
	    <input type="hidden" name="action" value="delete_all">

	    <table class="form-table">
	        <tbody>
	            <tr valign="top">
	                <th scope="row">
				        <input type="submit" name="Submit" class="button" value="<?php _e( 'Delete all videos', 'TRADUCIR' ); ?>">
	                </th>
	                <td>
	                    <p>
					        <?php _e( 'Be careful with this option - you will lose all links you have built between blog posts and the video pages. This is really only meant as a reset option.', 'TRADUCIR' ); ?>
					    </p>
	                </td>
	            </tr>
	        </tbody>
	    </table>
	</form>

	<h3><?php _e( 'Plugin settings', 'TRADUCIR' ); ?></h3>
	<p>
	    <?php _e( 'Description of plugin settings here!!!!!', 'TRADUCIR' ); ?>
	</p>
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	    <input type="hidden" name="plugin-video" value="ACTIVE">
	    <input type="hidden" name="action" value="videos_settings">
	    <table class="form-table">
	        <tbody>
	            <tr valign="top">
	                <th scope="row">
	                    <?php _e( 'RSS', 'TRADUCIR' ); ?>
	                </th>
	                <td>
	                    <fieldset>
	                        <legend class="screen-reader-text">
	                            <span><?php _e( 'RSS', 'TRADUCIR' ); ?></span>
	                        </legend>
	                    </fieldset>

	                    <label for="_rss">
	                        <!-- RSS option -->

	                        <input type="checkbox" name="_rss" id="_rss" value="rss" <?php if ( self::$rss == true ) echo 'checked'; ?>>

	                        <?php _e( 'Add video posts to website RSS feed', 'TRADUCIR' ); ?>
	                    </label>
	                </td>
	            </tr>

	            <tr valign="top">
	                <th scope="row">
	                    <?php _e( 'Upload', 'TRADUCIR' ); ?>
	                </th>
	                <td>
	                    <fieldset>
	                        <legend class="screen-reader-text">
	                            <span><?php _e( 'Upload', 'TRADUCIR' ); ?></span>
	                        </legend>
	                    </fieldset>

	                    <label for="_upload_condition">
							<!-- Video upload condition -->

	                        <input type="checkbox" name="_upload_condition" id="_upload_condition" value="upload_condition" <?php if ( ! self::$terms ) echo 'disabled'; ?> <?php if ( self::$upload_condition == true ) echo 'checked'; ?>>

	                        <?php _e( 'Post videos only if some category match the title', 'TRADUCIR' ); ?>

	                        <?php
	                            switch ( self::$terms ) {

	                                case true:
	                                    $_POST['action'] = 'videos_settings';
	                                break;

	                                case false:
	                                    $_POST['action'] = 'videos_settings';
	                                break;

	                            }
	                        ?>
	                    </label>
	                </td>
	            </tr>

	            <tr valign="top">
	                <th scope="row">
	                    <label for="_fetch_intervals">
	                        <?php _e( 'Update interval', 'TRADUCIR' ); ?>
	                    </label>
	                </th>
	                <td>
	                    <?php

	                    // Fetch shedule selection option
	                    $invervals = wp_get_schedules();

	                    echo '<select name="_fetch_intervals" id="_fetch_intervals">';

	                    foreach ( $invervals as $key => $value ) {

	                        $slug = $key;
	                        $name = $value['display'];
	                        echo '<option value="' . esc_attr( $slug ) . '" ' . selected( self::$fetch_intervals, $slug, false ) . '>' . esc_html( $name ) . '</option>';

	                    }

	                    echo '</select>';

	                    ?>
	                </td>
	            </tr>

	            <tr valign="top">
	                <th scope="row">
	                    <label for="_post_type_select">
	                        <?php _e( 'Post type', 'TRADUCIR' ); ?>
	                    </label>
	                </th>
	                <td>
	                    <?php

	                    // Post-type selection option
	                    $post_types = array_merge( get_post_types( array( '_builtin' => false ), 'objects' ), get_post_types( array( '_builtin' => true ), 'objects' ) );

	                    echo '<select name="_post_type_select" id="_post_type_select">';

	                    foreach ( $post_types as $key ) {

	                        $slug = $key->name;
	                        $name = $key->labels->name;

	                        echo '<option value="' . esc_attr( $slug ) . '" ' . selected( self::$post_type_select, $slug, false ) . '>' . esc_html( $name ) . '</option>';

	                    }

	                    echo '</select>';

	                    ?>
	                </td>
	            </tr>

	            <tr valign="top">
	                <th scope="row">
	                    <label for="_taxonomy_select">
	                        <?php _e( 'Taxonomy', 'TRADUCIR' ); ?>
	                    </label>
	                </th>
	                <td>
	                    <?php

	                    // Taxonomy selection option
	                    $taxonomies = array_merge( get_taxonomies( array( '_builtin' => false ), 'objects' ), get_taxonomies( array( '_builtin' => true ), 'objects' ) );

	                    echo '<select name="_taxonomy_select" id="_taxonomy_select">';

	                    foreach ( $taxonomies as $key ) {

	                        $slug = $key->name;
	                        $name = $key->labels->name;

	                        echo '<option value="' . esc_attr( $slug ) . '" ' . selected( self::$taxonomy_select, $slug, false ). '>' . esc_html( $name ) . '</option>';

	                    }

	                    echo '</select>';

	                    ?>
	                </td>
	            </tr>

	            <tr valign="top">
	                <th scope="row">
	                    <label for="_post_status_select">
	                        <?php _e( 'Post status', 'TRADUCIR' ); ?>
	                    </label>
	                </th>
	                <td>
	                    <?php

	                    // Post status selection option
	                    $post_status_array = array( 'publish', 'pending', 'private', 'draft' );

	                    echo '<select name="_post_status_select" id="_post_status_select">';

	                    foreach ( $post_status_array as $key ) {

	                        $slug = $key;
	                        $name = ucfirst( $key );

	                        echo '<option value="' . esc_attr( $slug ) . '" ' .selected( self::$post_status_select, $slug, false ). '>' . esc_html( $name ) . '</option>';

	                    }

	                    echo '</select>';

	                    ?>
	                </td>
	            </tr>
	        </tbody>
	    </table>

	    <p class="submit">
	        <input type="submit" name="Submit" id="submit" class="button button-primary" value="<?php _e( 'Save changes' ); ?>">
	    </p>
	</form>

	<table class="wp-list-table widefat fixed">
	    <thead>
	        <tr>
	            <th scope="col" id="title" class="manage-column column-title"><span><?php _e( 'Authors', 'TRADUCIR' ); ?></span></th>
	            <th scope="col" id="host" class="manage-column column-host"><span><?php _e( 'Host', 'TRADUCIR' ); ?></span></th>
	        </tr>
	    </thead>
	    <tfoot>
	        <tr>
	            <th scope="col" class="manage-column column-title"><span><?php _e( 'Authors', 'TRADUCIR' ); ?></span></th>
	            <th scope="col" class="manage-column column-host"><span><?php _e( 'Host', 'TRADUCIR' ); ?></span></th>
	        </tr>
	    </tfoot>
	    <tbody id="the-list">
		    <?php if( self::$authors ) : ?>
			    <?php foreach ( self::$authors as $author ) : ?>
		        <tr valign="top">
		            <td class="column-title">
		                <strong>
		                    <a class="row-title" href="<?php echo 'https://www.' .self::$hosts[$author['host_id']]. '.com/' .$author['author_id']; ?>"><?php echo $author['author_id']; ?></a>
		                </strong>
		                <div class="row-actions">
		                    <span class="trash">
		                        <a href="#" id="<?php echo $author['author_id']; ?>" class="submitdelete" onclick="delete_author(<?php echo '\'' .$author['host_id']. '\', \'' .$author['author_id']. '\''; ?>);" ><?php echo __( 'Delete' ); ?>
		                        </a>
		                    </span>
		                </div>
		            </td>
		            <th scope="row">
		                <span><?php echo self::$hosts[$author['host_id']]; ?></span>
		            </th>
		        </tr>
			    <?php endforeach; ?>
		    <?php else: ?>
		    <tr class="no-items">
		    	<td class="colspanchange">No authors found.</td>
		    </tr>
			<?php endif; ?>
	    </tbody>
	</table>
</div>
