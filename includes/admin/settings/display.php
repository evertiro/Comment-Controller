<?php
/**
 * Display options page
 *
 * @package     CommentController\Admin\Settings\Display
 * @since       1.0.1
 * @author      Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Render the option page
 *
 * @since       1.0.0
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_options_page() {
    global $commentcontroller_options;

    ob_start();
    ?>
    <div class="wrap">
        <h2><?php _e( 'Comment Controller', 'comment-controller' ); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table">
                    <?php
                        settings_fields( 'commentcontroller_settings' );
                        do_settings_fields( 'commentcontroller_settings_general', 'commentcontroller_settings_general' );
                    ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}
