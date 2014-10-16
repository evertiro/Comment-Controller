<?php
/**
 * Admin pages
 *
 * @package     CommentController\Admin\Pages
 * @since       1.0.0
 * @author      Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Create the admin page
 *
 * @since       1.0.0
 * @return      void
 */
function commentcontroller_admin_page() {
    add_options_page( __( 'Comment Controller', 'comment-controller' ), __( 'Comment Controller', 'comment-controller' ), 'manage_options', 'commentcontroller', 'commentcontroller_options_page' );
}
add_action( 'admin_menu', 'commentcontroller_admin_page', 10 );
