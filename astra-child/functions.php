<?php
function my_theme_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Enqueue child theme styles
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
    // Check if the mec-overwrites.css file exists before enqueuing
    if (file_exists(get_stylesheet_directory() . '/css/mec-overwrites.css')) {
        wp_enqueue_style('mec-overwrites', get_stylesheet_directory_uri() . '/css/mec-overwrites.css', array('child-style'));
    } else {
        error_log('mec-overwrites.css file not found in the css folder.');
    }
}

function enqueue_custom_calendar_script() {
    wp_enqueue_script('custom-calendar', get_stylesheet_directory_uri() . '/js/custom-calendar.js', array('jquery'), null, true);

    // Pass the REST URL to the JavaScript file
    wp_localize_script('custom-calendar', 'mec_calendar', array(
        'rest_url' => get_rest_url() . 'mec/v1/events'
    ));
}
// register strings for translations:
    function register_my_strings() {
        if (function_exists('pll_register_string')) {
            pll_register_string('events-planned', 'Planowane wydarzenia', 'astra-child');
            pll_register_string('premiere-date', 'Data premiery', 'astra-child');
            pll_register_string('duration', 'Czas trwania', 'astra-child');
            pll_register_string('tickets', 'Bilety', 'astra-child');
            pll_register_string('trigger-warnings', 'Ostrzegacze', 'astra-child');
            pll_register_string('premiere', 'premiera', 'astra-child');
            pll_register_string('sold-out', 'wyprzedane', 'astra-child');
            pll_register_string('buy-ticket', 'Kup bilet', 'astra-child');
            pll_register_string('location-label', 'Lokalizacja', 'astra-child');
            pll_register_string('accessibility-info', 'informacje o dostępności dla danego wydarzenia', 'astra-child');
                    // From custom-mec-functions.php

            pll_register_string('show', 'Pokaż', 'astra-child');
            pll_register_string('chronologically', 'CHRONOLOGICZNIE', 'astra-child');
            pll_register_string('alphabetically', 'ALFABETYCZNIE', 'astra-child');
            pll_register_string('no-events', 'Nie znaleziono wydarzeń.', 'astra-child');
            pll_register_string('back-to-events', '← POWRÓT DO WYDARZEŃ', 'astra-child');
            pll_register_string('tickets-available', 'Dostępne bilety dla:', 'astra-child');
            pll_register_string('free', 'darmowe', 'astra-child');
            pll_register_string('choose-date', 'Wybierz datę', 'astra-child');
        }
    }
    add_action('init', 'register_my_strings');

// function load_child_theme_textdomain() {
//     load_child_theme_textdomain('astra-child', get_stylesheet_directory() . '/languages');
// }

// add_action('after_setup_theme', 'load_child_theme_textdomain');

add_action('wp_enqueue_scripts', 'enqueue_custom_calendar_script');

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

// Include custom MEC functions
require_once get_stylesheet_directory() . '/custom-mec-functions.php';
require_once get_stylesheet_directory() . '/komuna-shortcodes.php';

?>