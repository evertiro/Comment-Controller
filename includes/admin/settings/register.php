<?php
/**
 * Register settings
 *
 * @package     CommentController\Admin\Settings\Register
 * @since       1.0.0
 * @author      Daniel J Griffiths <dgriffiths@section214.com>
 * @copyright   Copyright (c) 2014, Daniel J Griffiths
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Setup plugin tabs
 *
 * @since       1.0.0
 * @return      array $tabs The predefined tabs
 */
function commentcontroller_get_settings_tabs() {
    $tabs               = array();
    $tabs['general']    = __( 'General', 'commentcontroller' );

    return apply_filters( 'commentcontroller_settings_tabs', $tabs );
}


/**
 * Setup the plugin settings
 *
 * @since       1.0.0
 * @return      array
 */
function commentcontroller_get_registered_settings() {
    // Whitelisted settings
    $commentcontroller_settings = array(
        // General settings
        'general' => apply_filters( 'commentcontroller_settings_general',
            array(
                array(
                    'id'    => 'disabled_post_types',
                    'name'  => __( 'Disable On Post Types', 'comment-controller' ),
                    'type'  => 'multicheck',
                    'desc'  => __( 'Specify post types to disable comments on', 'comment-controller' ),
                    'options'   => commentcontroller_get_post_types()
                ),
                array(
                    'id'    => 'disabled_roles',
                    'name'  => __( 'Disable On Roles', 'comment-controller' ),
                    'type'  => 'multicheck',
                    'desc'  => __( 'Specify roles to disable comments for', 'comment-controller' ),
                    'options'   => commentcontroller_get_roles()
                )
            )
        )
    );

    return apply_filters( 'commentcontroller_registered_settings', $commentcontroller_settings );
}


/**
 * Retrieve a given option
 *
 * @since       1.0.0
 * @param       string $key The key to retrieve
 * @param       mixed $default The default to retrieve if not found
 * @global      array $commentcontroller_options The Comment Controller options array
 * @return      mixed The value of the given key
 */
function commentcontroller_get_option( $key = '', $default = false ) {
    global $commentcontroller_options;

    $value = ! empty( $commentcontroller_options[$key] ) ? $commentcontroller_options[$key] : $default;
    $value = apply_filters( 'commentcontroller_get_option', $value, $key, $default );

    return apply_filters( 'commentcontroller_get_option_' . $key, $value, $key, $default );
}


/**
 * Retrieve all settings
 *
 * @since       1.0.0
 * @return      array All Comment Controller settings
 */
function commentcontroller_get_settings() {
    $settings = get_option( 'commentcontroller_settings' );

    if( empty( $settings ) ) {
        update_option( 'commentcontroller_settings', array() );
    }

    return apply_filters( 'commentcontroller_get_settings', $settings );
}


/**
 * Add all settings sections and fields
 *
 * @since       1.0.0
 * @return      void
 */
function commentcontroller_register_settings() {
    if( false == get_option( 'commentcontroller_settings' ) ) {
        add_option( 'commentcontroller_settings' );
    }

    foreach( commentcontroller_get_registered_settings() as $tab => $settings ) {
        add_settings_section(
            'commentcontroller_settings_' . $tab,
            __return_null(),
            '__return_false',
            'commentcontroller_settings_' . $tab
        );

        foreach( $settings as $option ) {
            $name = isset( $option['name'] ) ? $option['name'] : '';

            add_settings_field(
                'commentcontroller_settings[' . $option['id'] . ']',
                $name,
                function_exists( 'commentcontroller_' . $option['type'] . '_callback' ) ? 'commentcontroller_' . $option['type'] . '_callback' : 'commentcontroller_missing_callback',
                'commentcontroller_settings_' . $tab,
                'commentcontroller_settings_' . $tab,
                array(
                    'section'   => $tab,
                    'id'        => isset( $option['id'] ) ? $option['id'] : null,
                    'desc'      => ! empty( $option['desc'] ) ? $option['desc'] : '',
                    'name'      => isset( $option['name'] ) ? $option['name'] : null,
                    'size'      => isset( $option['size'] ) ? $option['size'] : null,
                    'options'   => isset( $option['options'] ) ? $option['options'] : '',
                    'std'       => isset( $option['std'] ) ? $option['std'] : '',
                    'min'       => isset( $option['min'] ) ? $option['min'] : null,
                    'max'       => isset( $option['max'] ) ? $option['max'] : null,
                    'step'      => isset( $option['step'] ) ? $option['step'] : null
                )
            );
        }
    }

    register_setting( 'commentcontroller_settings', 'commentcontroller_settings', 'commentcontroller_settings_sanitize' );
}
add_action( 'admin_init', 'commentcontroller_register_settings' );


/**
 * Get pages
 *
 * @since       1.0.0
 * @param       bool $force Force pages to be loaded
 * @return      array $pages An array of all pages
 */
function commentcontroller_get_pages( $force = false ) {
    $pages = array( 0 => '' );

    if( ( ! isset( $_GET['page'] ) || 'commentcontroller-settings' != $_GET['page'] ) && ! $force ) {
        return $pages;
    }

    $all_pages = get_pages();

    if( $all_pages ) {
        foreach( $all_pages as $page ) {
            $pages[$page->ID] = $page->post_title;
        }
    }

    return $pages;
}


/**
 * Sanitization
 *
 * @since       1.0.0
 * @param       array $input The value entered in the field
 * @global      array $commentcontroller_options The Comment Controller options array
 * @return      array $output The sanitized values
 */
function commentcontroller_settings_sanitize( $input = array() ) {
    global $commentcontroller_options;

    if( empty( $_POST['_wp_http_referer'] ) ) {
        return $input;
    }

    parse_str( $_POST['_wp_http_referer'], $referrer );

    $settings   = commentcontroller_get_registered_settings();
    $tab        = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

    $input = $input ? $input : array();
    $input = apply_filters( 'commentcontroller_settings_' . $tab . '_sanitize', $input );

    // Loop through settings and pass through sanitization filter
    foreach( $input as $key => $value ) {
        // Get the setting type
        $type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

        if( $type ) {
            // Type specific filter
            $input[$key] = apply_filters( 'commentcontroller_settings_sanitize_' . $type, $value, $key );
        }

        // General filter
        $input[$key] = apply_filters( 'commentcontroller_settings_sanitize', $input[$key], $key );
    }

    // Unset empty values
    if( ! empty( $settings[$tab] ) ) {
        foreach( $settings[$tab] as $key => $value ) {
            if( is_numeric( $key ) ) {
                $key = $value['id'];
            }

            if( empty( $input[$key] ) ) {
                unset( $commentcontroller_options[$key] );
            }
        }
    }

    $output = array_merge( $commentcontroller_options, $input );

    add_settings_error( 'commentcontroller-notices', '', __( 'Settings updated.', 'commentcontroller' ), 'updated' );

    return $output;
}


/**
 * Sanitize text fields
 *
 * @since       1.0.0
 * @param       string $input The field value
 * @return      string $input The sanitized value
 */
function commentcontroller_sanitize_text_field( $input ) {
    return trim( $input );
}
add_filter( 'commentcontroller_settings_sanitize_text', 'commentcontroller_sanitize_text_field' );


/**
 * Header callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the settings
 * @return      void
 */
function commentcontroller_header_callback( $args ) {
    echo '<hr />';
}


/**
 * Checkbox callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_checkbox_callback( $args ) {
    global $commentcontroller_options;

    $checked = isset( $commentcontroller_options[$args['id']] ) ? checked( 1, $commentcontroller_options[$args['id']], false ) : '';

    $html  = '<input type="checkbox" id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>&nbsp;';
    $html .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Color picker callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_color_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $default = isset( $args['std'] ) ? $args['std'] : '';
    $size    = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

    $html    = '<input type="text" class="commentcontroller-color-picker" id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />&nbsp;';
    $html   .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Color select callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_color_select_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $html = '<select id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']">';

    foreach( $args['options'] as $option => $color ) {
        $selected  = selected( $option, $value, false );
        $html     .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
    }

    $html .= '</select>&nbsp;';
    $html .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Descriptive text callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @return      void
 */
function commentcontroller_descriptive_text_callback( $args ) {
    echo esc_html( $args['desc'] );
}


/**
 * Multicheck callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_multicheck_callback( $args ) {
    global $commentcontroller_options;

    if( ! empty( $args['options'] ) ) {
        foreach( $args['options'] as $key => $option ) {
            $enabled = isset( $commentcontroller_options[$args['id']][$key] ) ? $option : null;

            echo '<input name="commentcontroller_settings[' . $args['id'] . '][' . $key . ']" id="commentcontroller_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';
            echo '<label for="commentcontroller_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br />';
        }

        echo '<p class="description">' . $args['desc'] . '</p>';
    }
}


/**
 * Multiselect callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_multiselect_callback( $args ) {
    global $commentcontroller_options;

    if( ! empty( $args['options'] ) ) {
        if( isset( $commentcontroller_options[$args['id']] ) ) {
            $value = $commentcontroller_options[$args['id']];
        } else {
            $value = isset( $args['std'] ) ? $args['std'] : '';
        }

        $html = '<select id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']" multiple>';

        foreach( $args['options'] as $option => $name ) {
            $selected  = selected( $option, $value, false );
            $html     .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
        }

        $html .= '</select>&nbsp;';
        $html .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

        echo $html;
    }
}


/**
 * Number callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_number_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $max    = isset( $args['max'] ) ? $args['max'] : 999999;
    $min    = isset( $args['min'] ) ? $args['min'] : 0;
    $step   = isset( $args['step'] ) ? $args['step'] : 1;

    $size   = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

    $html   = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>&nbsp;';
    $html  .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Password callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_password_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $size   = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

    $html   = '<input type="password" class="' . $size . '-text" id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>&nbsp;';
    $html  .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Radio callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_radio_callback( $args ) {
    global $commentcontroller_options;

    foreach( $args['options'] as $key => $option ) {
        $checked = false;

        if( isset( $commentcontroller_options[$args['id']] ) && $commentcontroller_options[$args['id']] == $key ) {
            $checked = true;
        } elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $commentcontroller_options[$args['id']] ) ) {
            $checked = true;
        }

        echo '<input name="commentcontroller_settings[' . $args['id'] . ']" id="commentcontroller_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
        echo '<label for="commentcontroller_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br />';
    }

    echo '<p class="description">' . $args['desc'] . '</p>';
}


/**
 * Rich editor callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_rich_editor_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $rows = isset( $args['size'] ) ? $args['size'] : 20;

    if( function_exists( 'wp_editor' ) ) {
        ob_start();
        wp_editor( stripslashes( $value ), 'commentcontroller_settings_' . $args['id'], array( 'textarea_name' => 'commentcontroller_settings[' . $args['id'] . ']', 'textarea_rows' => $rows ) );
        $html = ob_get_clean();
    } else {
        $html = '<textarea class="large-text" rows="10" id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']>' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    }

    $html .= '<br /><label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Select callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_select_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $html = '<select id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']">';

    foreach( $args['options'] as $option => $name ) {
        $selected  = selected( $option, $value, false );
        $html     .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
    }

    $html .= '</select>&nbsp;';
    $html .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Text callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_text_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $size   = ( isset( $args['size'] ) && is_null( $args['size'] ) ) ? $args['size'] : 'regular';

    $html   = '<input type="text" class="' . $size . '-text" id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>&nbsp;';
    $html  .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Textarea callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of Comment Controller options
 * @return      void
 */
function commentcontroller_textarea_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $html  = '<textarea class="large-text" cols="50" rows="5" id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>&nbsp;';
    $html .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Upload callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @global      array $commentcontroller_options Array of all the Comment Controller options
 * @return      void
 */
function commentcontroller_upload_callback( $args ) {
    global $commentcontroller_options;

    if( isset( $commentcontroller_options[$args['id']] ) ) {
        $value = $commentcontroller_options[$args['id']];
    } else {
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }

    $size    = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

    $html    = '<input type="text" class="' . $size . '-text" id="commentcontroller_settings[' . $args['id'] . ']" name="commentcontroller_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '">';
    $html   .= '<span>&nbsp;<input type="button" class="commentcontroller_settings_upload_button button-secondary" value="' . __( 'Upload File', 'commentcontroller' ) . '" /></span>&nbsp;';
    $html   .= '<label for="commentcontroller_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Hook callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @return      void
 */
function commentcontroller_hook_callback( $args ) {
    do_action( 'commentcontroller_' . $args['id'] );
}


/**
 * Missing callback
 *
 * @since       1.0.0
 * @param       array $args Arguments passed by the setting
 * @return      void
 */
function commentcontroller_missing_callback( $args ) {
    printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'commentcontroller' ), $args['id'] );
}
