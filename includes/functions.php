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
 * @return      bool $return True if shown, false otherwise
 */
function commentcontroller_maybe_show_comments( $user_id ) {
    $user = get_userdata( $user_id );

    if( $user->commentcontroller_disable ) {
        $return = false;
    } else {
        $return = true;
    }

    return $return;
}


/**
 * Disable comments?
 *
 * @since       1.0.0
 * @param       bool $open Comment status
 * @param       int $post_id The post ID
 * @return      bool $open Whether to display comments or not
 */
function commentcontroller_show_comments( $open, $post_id ) {
    if( is_user_logged_in() ) {
        $user_id = get_current_user_id();

        if( ! commentcontroller_maybe_show_comments( $user_id ) ) {
            $open = false;
        }
    }
    
    return $open;
}
add_filter( 'comments_open', 'commentcontroller_show_comments', 999, 2 );
