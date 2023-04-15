<?php
/*
Plugin Name: Fastway Track and Trace
Description: A simple plugin to track Fastway parcels using a shortcode [fastway_tracking].
Version: 1.0
Author: Byron Jacobs
Author URI: https://byronjacobs.co.za
License: GPLv2 or later
Text Domain: fastway-track-and-trace
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Settings page
function fastway_settings_init()
{
    register_setting('fastway_settings', 'fastway_api_key');
    register_setting('fastway_settings', 'fastway_show_logo'); 

    add_settings_section(
        'fastway_settings_section',
        __('Settings', 'fastway-track-and-trace'),
        'fastway_settings_section_callback',
        'fastway_settings'
    );

    add_settings_field(
        'fastway_api_key',
        __('Fastway API Key', 'fastway-track-and-trace'),
        'fastway_api_key_render',
        'fastway_settings',
        'fastway_settings_section'
    );

    add_settings_field(
        'fastway_show_logo',
        __('Show Fastway Logo', 'fastway-track-and-trace'),
        'fastway_show_logo_render',
        'fastway_settings',
        'fastway_settings_section'
    );
}

function fastway_settings_section_callback()
{
    echo __('Enter your Fastway API Key and use the shortcode [fastway_tracking] on any page or post where you want to display the tracking form.', 'fastway-track-and-trace');
    echo '<br><br>';
    echo __('You can obtain your API Key at <a href="https://sa.api.fastway.org/v3/Docs/GetAPIKey" target="_blank">https://sa.api.fastway.org/v3/Docs/GetAPIKey</a>.', 'fastway-track-and-trace');
    echo '<br><br>';
    echo __('For additional functionality or support, please email <a href="mailto:hello@byronjacobs.co.za">hello@byronjacobs.co.za</a>.', 'fastway-track-and-trace');
}


function fastway_api_key_render()
{
    $options = get_option('fastway_api_key');
    echo '<input type="text" name="fastway_api_key" value="' . $options . '" />';
}
function fastway_show_logo_render()
{
    $options = get_option('fastway_show_logo');
    echo '<input type="checkbox" name="fastway_show_logo" value="1"' . checked(1, $options, false) . ' />';
}

add_action('admin_init', 'fastway_settings_init');

function fastway_options_page()
{
    add_options_page(
        __('Fastway Track and Trace', 'fastway-track-and-trace'),
        __('Fastway Track and Trace', 'fastway-track-and-trace'),
        'manage_options',
        'fastway_settings',
        'fastway_options_page_html'
    );
}

function fastway_options_page_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    settings_errors('fastway_messages');

    echo '<div class="wrap">';
    echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
    echo '<form action="options.php" method="post">';
    settings_fields('fastway_settings');
    do_settings_sections('fastway_settings');
    submit_button('Save Settings');
    echo '</form></div>';
}

add_action('admin_menu', 'fastway_options_page');

// Shortcode
function fastway_tracking_shortcode()
{
    wp_enqueue_script('fastway-ajax', plugin_dir_url(__FILE__) . 'fastway-ajax.js', array('jquery'), '1.0', true);
    wp_localize_script('fastway-ajax', 'fastway_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'fastway_api_key' => get_option('fastway_api_key')));

    $output = '<div id="fastway-tracking-container">';
    if (get_option('fastway_show_logo')) {
        $output .= '<div id="fastway-logo"><img src="' . plugin_dir_url(__FILE__) . 'fastway-logo.png" alt="Fastway Logo" id="fastway-logo" /></div>';
    }
    $output .= '<input type="text" id="fastway-waybill" placeholder="Enter Waybill Number" />';
    $output .= '<button id="fastway-submit">' . __('Track', 'fastway-track-and-trace') . '</button>';
    $output .= '<div id="fastway-spinner"></div>'; // Spinner
    $output .= '<div id="fastway-tracking-text" data-text="' . __('Tracking', 'fastway-track-and-trace') . '"></div>'; // Tracking text
    $output .= '<div id="fastway-result"></div>';
    $output .= '</div>';

    return $output;
}

add_shortcode('fastway_tracking', 'fastway_tracking_shortcode');

// AJAX callback
function fastway_ajax_callback()
{
    $waybill = isset($_POST['waybill']) ? sanitize_text_field($_POST['waybill']) : '';
    $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';

    if (!empty($waybill) && !empty($api_key)) {
        $url = 'https://sa.api.fastway.org/v3/tracktrace/detail?labelNo=' . $waybill . '&api_key=' . $api_key;
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            echo json_encode(array('error' => 'Unable to fetch data.'));
        } else {
            echo $response['body'];
        }
    } else {
        echo json_encode(array('error' => 'Invalid data.'));
    }

    wp_die();
}

add_action('wp_ajax_fastway_ajax', 'fastway_ajax_callback');
add_action('wp_ajax_nopriv_fastway_ajax', 'fastway_ajax_callback');

// Enqueue styles
function fastway_enqueue_styles()
{
    wp_enqueue_style('fastway-styles', plugin_dir_url(__FILE__) . 'fastway-styles.css');

}

add_action('wp_enqueue_scripts', 'fastway_enqueue_styles');
