<?php
/**
 * Help functions.
 *
 * @package   Pull_Automatically_Videos
 * @author    Matias Esteban <estebanmatias92@gmail.com>
 * @license   MIT License
 * @link      http://example.com
 * @copyright 2013 Matias Esteban
 */

if ( ! function_exists( 'unregister_post_type' ) ) :

/**
 * Unregister custom post-type.
 *
 * @since 0.1.0
 *
 * @param string    $post_type Post-type slug.
 *
 * @return boolean    If post-type has been removed, returns true, else, returns false.
 */
function unregister_post_type( $post_type ) {

    do_action( 'unregister_post_type', $post_type );

    global $wp_post_types;

    if ( isset( $wp_post_types[ $post_type ] ) ) {
        unset( $wp_post_types[ $post_type ] );
        return true;
    }

    return false;

}

endif;


if ( ! function_exists( 'unregister_post_type2' ) ) :

/*
 * Usage for a custom post type named 'movies':
 * unregister_post_type( 'movies' );
 *
 * Usage for the built in 'post' post type:
 * unregister_post_type( 'post', 'edit.php' );
*/
function unregister_post_type2( $post_type, $slug = '' ) {

    global $wp_post_types;

    if ( isset( $wp_post_types[ $post_type ] ) ) {
            unset( $wp_post_types[ $post_type ] );

            $slug = ( ! $slug ) ? 'edit.php?post_type=' . $post_type : $slug;
            remove_menu_page( $slug );
    }

}

endif;


if ( ! function_exists( 'unregister_taxonomy' ) ) :

/**
 * Unregister custom taxonomy.
 *
 * @since 0.1.0
 *
 * @param string    $taxonomy Taxonomy slug.
 *
 * @return boolean    If taxonomy has been removed, return true, else, return false.
 */
function unregister_taxonomy( $taxonomy ) {

    do_action( 'unregister_taxonomy', $taxonomy );

    global $wp_taxonomies;

    if ( isset( $wp_taxonomies[ $taxonomy ] ) ) {
        unset( $wp_taxonomies[ $taxonomy ] );
        return true;
    }

    return false;

}

endif;


if ( ! function_exists( 'delete_post_type_taxonomies' ) ) :

/**
 * Remove taxonomies from a post-type
 *
 * @since 0.1.0
 *
 * @param string    $post_type Post-type slug to remove his taxonomies.
 *
 * @return boolean    Returns false if it's no running on admin, if the taxonomies are removed successfully, returns true, else returns false.
 */
function delete_post_type_taxonomies( $post_type ) {

    if ( ! is_admin() )
        return false;

    add_action( 'unregister_taxonomy', 'delete_taxonomy_terms', 10 );
    $taxonomies = get_object_taxonomies( $post_type, 'names' );

    if ( $taxonomies ) {
        foreach ( $taxonomies as $taxonomy ) {
            unregister_taxonomy( $taxonomy );
        }

        return true;
    }

    return false;

}

endif;


if ( ! function_exists( 'delete_taxonomy_terms' ) ) :

/**
 * Remove all terms of a taxonomy.
 *
 * @since 0.1.0
 *
 * @param string    $taxonomy Taxonomy to remove his terms.
 *
 * @return boolean    Returns false if it's no running on admin, if the terms are removed successfully, returns true, else returns false.
 */
function delete_taxonomy_terms( $taxonomy ) {

    if ( ! is_admin() )
        return false;

    $terms = get_terms( $taxonomy, array( 'fields' => 'ids', 'hide_empty' => false ) );

    if ( $terms ) {
        foreach ( $terms as $value ) {
            wp_delete_term( $value, $taxonomy );
        }

        return true;
    }

    return false;

}

endif;


if ( ! function_exists( 'delete_post_type_posts' ) ) :

/**
 * Delete posts from a post-type.
 *
 * @since 0.1.0
 *
 * @param string    $post_type    Post-type slug to removed his posts.
 * @param boolean   $force_delete If it's true, the posts are remove permanently, else, the posts go to the trash.
 *
 * @return boolean    Returns false if it's no running on admin, if the posts are removed successfully, returns true, else returns false.
 */
function delete_post_type_posts( $post_type, $force_delete = false, $delete_attach = false ) {

    if ( ! is_admin() )
        return false;

    $posts = get_posts( array( 'post_type' => $post_type, 'nopaging' => true ) );

    if ( $posts ) {
        foreach ( $posts as $post ) {
            if ( $delete_attach )
                delete_post_attachements( $post->ID, $force_delete );
            wp_delete_post( $post->ID, $force_delete );
        }

        return true;
    }

    return false;

}

endif;


if ( ! function_exists( 'delete_post_attachements' ) ) :

/**
 * Delete all attachments from a post.
 *
 * @since 0.1.0
 *
 * @param integer   $post_id      Attachment ID to remove.
 * @param boolean   $force_delete If it's true, the attachments are remove permanently, else, the attachments go to the trash.
 *
 * @return boolean    Returns false if it's no running on admin, if the attachements are removed successfully, returns true, else returns false.
 */
function delete_post_attachements( $post_id, $force_delete = false ) {


    if ( ! is_admin() )
        return false;

    $attachments = get_children( array(
        'post_parent' => $post_id,
        'post_type'   => 'attachment',
        'numberposts' => -1,
        'post_status' => 'any'
    ) );

    if ( $posts ) {
        foreach ( $posts as $post ) {
            wp_delete_attachment( $post_id, $force_delete );
        }

        return true;
    }

    return false;

}

endif;


if ( ! function_exists( 'remove_page_and_menu' ) ) :

/**
 * Remove the page, and his menu item.
 *
 * @since 0.1.0
 *
 * @param string    $name         Page name to remove.
 * @param boolean   $force_delete If it's true, the page is remove permanently, else, the page go to the trash.
 *
 * @return boolean    Returns false if it is not running on admin, if the page is removed successfully, returns true, else returns false.
 */
function remove_page_and_menu( $name, $force_delete = false ) {

    /**
     * Error when delete pages, menu item doesn't remove (possible theme hook problem).
     */

    if ( ! is_admin() )
        return false;

    // Delete menu
    if ( $force_delete ) {
        add_action( 'deleted_post', '_wp_delete_post_menu_item' );
    } else {
        add_action( 'wp_trash_post', '_wp_delete_post_menu_item' );
    }

    $page = get_page_by_title( $name );

    // Delete page
    if ( $page ) {
        wp_delete_post( $page->ID, $force_delete );
        return true;
    }

    return false;

}

endif;


if ( ! function_exists( 'add_page_and_menu' ) ) :

/**
 * Add page and menu item automatically.
 *
 * @since 0.1.0
 *
 * @param string    $name          Page name to add
 * @param string    $template_path Path to the template for set it on the new page created.
 */
function add_page_and_menu( $name, $template_path ) {

    // Insert the page
    $page = get_page_by_title( $name );

    if ( ! $page ) {

        $post_args['post_title']  = $name;
        $post_args['post_type']   = 'page';
        $post_args['post_status'] = 'publish';

        $post_id = wp_insert_post( $post_args );

        update_post_meta( $post_id, '_wp_page_template', $template_path );

    } else {
        update_post_meta( $page->ID, '_wp_page_template', $template_path );
    }

}

endif;
