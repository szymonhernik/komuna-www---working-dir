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
            pll_register_string('all-day', 'Cały dzień', 'astra-child');
        }
    }
    add_action('init', 'register_my_strings');


// add_action('wp_enqueue_scripts', 'enqueue_custom_calendar_script');

// add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

// Include custom MEC functions
require_once get_stylesheet_directory() . '/custom-mec-functions-v2.php';
require_once get_stylesheet_directory() . '/komuna-shortcodes.php';

function custom_yoast_breadcrumbs($links) {
    if (is_singular('mec-events')) {
        $post_id = get_the_ID();
        $categories = wp_get_post_terms($post_id, 'mec_category');

        if (!empty($categories) && !is_wp_error($categories)) {
            $category = $categories[0];
            
            // Remove the default breadcrumb link for 'wydarzenia'
            array_splice($links, 1, 1);

            // Determine the correct path based on current language
            $program_path = (pll_current_language() === 'en') ? 'programme' : 'program';
            
            $program_page = get_page_by_path($program_path);
            if ($program_page) {
                $links[] = array(
                    'url' => get_permalink($program_page->ID),
                    'text' => get_the_title($program_page->ID)
                );
            }

            // Use the same language path for category
            $category_page = get_page_by_path($program_path . '/' . $category->slug);
            if ($category_page) {
                $links[] = array(
                    'url' => get_permalink($category_page->ID),
                    'text' => get_the_title($category_page->ID)
                );
            }

            // Add current event
            $links[] = array(
                'url' => get_permalink($post_id),
                'text' => get_the_title($post_id)
            );
        }
    }
    return $links;
}
add_filter('wpseo_breadcrumb_links', 'custom_yoast_breadcrumbs');



?>