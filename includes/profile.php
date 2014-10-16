<?php
/**
 * Modify user profiles
 *
 * @package     CommentController\Profile
 * @since       1.0.0
 * @author      Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Modify the output of profile.php
 *
 * @since       1.0.0
 * @param       object $user The current user info
 * @return      void
 */
function commentcontroller_profile_field( $user ) {
    $user = get_userdata( $user->ID );

    if( commentcontroller_maybe_show_profile_option() ) {
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="commentcontroller-disable"><?php _e( 'Disable Comments', 'comment-controller' ); ?></label>
                    </th>
                    <td>
                        <input name="commentcontroller-disable" type="checkbox" id="commentcontroller-disable" value="1" <?php checked( 1, $user->commentcontroller_disable, true ); ?>/>
                        <span class="description"><?php _e( 'Disable output of the comment field and all existing comments.', 'comment-controller' ); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}
add_action( 'show_user_profile', 'commentcontroller_profile_field' );
add_action( 'edit_user_profile', 'commentcontroller_profile_field' );


/**
 * Process field updates on save
 *
 * @since       1.0.0
 * @param       int $user_id The ID of a given user
 * @return      void
 */
function commentcontroller_update_field( $user_id ) {
    if( current_user_can( 'edit_user', $user_id ) && isset( $_POST['commentcontroller-disable'] ) ) {
        update_user_meta( $user_id, 'commentcontroller_disable', true );
    } else {
        delete_user_meta( $user_id, 'commentcontroller_disable' );
    }
}
add_action( 'personal_options_update', 'commentcontroller_update_field' );
add_action( 'edit_user_profile_update', 'commentcontroller_update_field' );
