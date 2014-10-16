<?php
/**
 * Helper functions
 *
 * @package     CommentController\Functions
 * @since       1.0.0
 * @author      Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Check if comments should be shown for a given user
 *
 * @since       1.0.0
 * @param       int $user_id The ID to check
 * @param       int $post_id The post we are viewing
 * @return      bool $return True if shown, false otherwise
 */
function commentcontroller_maybe_show_comments( $user_id, $post_id = false ) {
    $user   = get_userdata( $user_id );
    $return = true;

    if( $post_id ) {
        $post_type  = get_post_type();
        $post_types = commentcontroller_get_option( 'disabled_post_types', array() );
        $author_id  = get_the_author_meta( 'ID' );
        $author     = get_userdata( $author_id );

        if( $author->commentcontroller_disallow ) {
            $return = false;
        }

        if( in_array( $post_type, $post_types ) ) {
            $return = false;
        }
    }

    $roles = commentcontroller_get_option( 'disabled_roles', array() );

    if( ! is_user_logged_in() ) {
        if( array_key_exists( 'guest', $roles ) ) {
            $return = false;
        }
    } else {
        foreach( $roles as $role => $name ) {
            if( current_user_can( $role ) ) {
                $return = false;
            }
        }
        
        if( $user->commentcontroller_disable ) {
            $return = false;
        }
    }

    return $return;
}


/**
 * Should users see the comment field?
 *
 * @since       1.0.1
 * @return      bool $return True if shown, false otherwise
 */
function commentcontroller_maybe_show_profile_option() {
    $return = true;
    $roles  = commentcontroller_get_option( 'disabled_roles', array() );

    foreach( $roles as $role => $name ) {
        if( current_user_can( $role ) ) {
            $return = false;
        }
    }

    return $return;
}


/**
 * Show comments?
 *
 * @since       1.0.0
 * @global      object $post The post we are viewing
 * @global      object $wp_query The WordPress query object
 * @return      mixed
 */
function commentcontroller_show_comments() {
    global $post, $wp_query;
    
    $user_id = get_current_user_id();

    if( ! commentcontroller_maybe_show_comments( $user_id, $post->ID ) ) {
        $wp_query->comments = array();
        $wp_query->comments_by_type = array();
        $wp_query->comment_count = '0';
        $wp_query->post->comment_count = '0';
        $wp_query->post->comment_status = 'closed';
        $wp_query->queried_object->comment_count = '0';
        $wp_query->queried_object->comment_status = 'closed';

        return COMMENTCONTROLLER_DIR . 'templates/comments.php';
    }
}
add_action( 'comments_template', 'commentcontroller_show_comments' );


/**
 * Show comment form?
 *
 * @since       1.0.1
 * @param       bool $open Comment status
 * @param       int $post_id The post ID
 * @return      bool $open Whether to display the comment form or not
 */
function commentcontroller_show_comment_form( $open, $post_id ) {
    $user_id = get_current_user_id();

    if( ! commentcontroller_maybe_show_comments( $user_id ) ) {
        $open = false;
    }

    return $open;
}
add_filter( 'comments_open', 'commentcontroller_show_comment_form', 999, 2 );


/**
 * Get valid post types
 *
 * @since       1.0.1
 * @return      array $post_types The valid post types
 */
function commentcontroller_get_post_types() {
    $post_types = array();

    $raw_post_types = get_post_types( array(
        'public'    => true
    ) );

    unset( $raw_post_types['attachment'] );

    foreach( $raw_post_types as $post_type ) {
        $object = get_post_type_object( $post_type );

        if( $object->labels->singular_name ) {
            $post_types[$post_type] = $object->labels->singular_name;
        } else {
            $post_types[$post_type] = $post_type;
        }
    }

    return $post_types;
}


/**
 * Get user roles
 *
 * @since       1.0.1
 * @return      array $roles The user roles
 */
function commentcontroller_get_roles() {
    global $wp_roles;

    $roles = $wp_roles->get_names();
    $roles['guest'] = __( 'Guest', 'comment-controller' );

    return $roles;
}
