<?php

function get_event_field_value($event, $field_number) {
    $event_id = $event->ID;
    $event_date = $event->date['start']['date'] ?? null;

    // Check if event is recurring
    $mec_repeat_status = (isset($event->data->meta['mec_repeat_status'])) 
        ? $event->data->meta['mec_repeat_status'] 
        : get_post_meta($event_id, 'mec_repeat_status', true);

    if ($mec_repeat_status == 0) {
        // Non-recurring event: Get field value from post's metadata
        return $event->data->meta['mec_fields'][$field_number] ?? null;
    } else {
        // Recurring event: Only get field value from edited occurrences
        return isset($event->data->edited_occurrences[$event_date]['fields'][$field_number]) 
            ? $event->data->edited_occurrences[$event_date]['fields'][$field_number] 
            : null;
    }
}

function get_event_premiere_status($event) {
    $event_id = $event->ID;
    $event_date = $event->date['start']['date'] ?? null;

    // Check if event is recurring
    $mec_repeat_status = (isset($event->data->meta['mec_repeat_status'])) 
        ? $event->data->meta['mec_repeat_status'] 
        : get_post_meta($event_id, 'mec_repeat_status', true);

    if ($mec_repeat_status == 0) {
        // Non-recurring event: Get premiere status from post's metadata
        return $event->data->meta['mec_fields'][16] ?? null;
    } else {
        // Recurring event: Only get premiere status from edited occurrences
        return isset($event->data->edited_occurrences[$event_date]['fields'][16]) 
            ? $event->data->edited_occurrences[$event_date]['fields'][16] 
            : null;
    }
}


function get_event_ticket_link($event) {
    $event_id = $event->ID;
    $start_timestamp = (isset($event->date) && isset($event->date['start']) && isset($event->date['start']['timestamp'])) ? $event->date['start']['timestamp'] : null;

    // Get mec_repeat_status
    $mec_repeat_status = (isset($event->data) && isset($event->data->meta) && isset($event->data->meta['mec_repeat_status'])) ? $event->data->meta['mec_repeat_status'] : get_post_meta($event_id, 'mec_repeat_status', true);

    if ($mec_repeat_status == 0) {
        // Non-recurring event: Get ticket link and availability from post's metadata
        $ticket_link = get_post_meta($event_id, 'mec_fields_5', true);
        $tickets_availability = get_post_meta($event_id, 'mec_fields_12', true);
    } else {
        // Recurring event: Get ticket link and availability from occurrences
        $metadata = MEC_feature_occurrences::param($event_id, $start_timestamp, '*');
        $ticket_link = isset($metadata['fields'][5]) ? $metadata['fields'][5] : null;
        $tickets_availability = isset($metadata['fields'][12]) ? $metadata['fields'][12] : null;
    }

    // Check if the event is free
    $is_free = get_post_meta($event_id, 'mec_fields_17', true);

    // Determine if the event is sold out
    $is_sold_out = ($tickets_availability == 'wyprzedane') && $is_free !== 'tak';

    return [
        'ticket_link' => $ticket_link,
        'is_sold_out' => $is_sold_out,
        'is_free' => $is_free === 'tak'
    ];
}

function custom_mec_archival_output($shortcode_id) {
    // Fetch events for the given shortcode ID
    $events = MEC_main::get_shortcode_events($shortcode_id);

    //  For debugging: render everything in the events variable
    //  $output = '<pre>' . print_r($events, true) . '</pre>';

    //  return $output;

    // Get sorting preference from query parameter (default is 'year')
    $sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'year';

    // Initialize the output
    $output = '<div class="archive-events-container">';
    
    $output .= '<div class="sorting-options" role="navigation" aria-label="Opcje sortowania">';
    $output .= '<p>' . esc_html(pll__('Pokaż')) . '</p>';
    $output .= '<ul class="sort-list">';
    $output .= '<li><a href="?sort=year" class="sort-link' . ($sort_option === 'year' ? ' sort-active' : '') . '" ' . ($sort_option === 'year' ? 'aria-current="page"' : '') . '>' . esc_html(pll__('CHRONOLOGICZNIE')) . '</a></li>';
    $output .= '<li><a href="?sort=alphabetical" class="sort-link' . ($sort_option === 'alphabetical' ? ' sort-active' : '') . '" ' . ($sort_option === 'alphabetical' ? 'aria-current="page"' : '') . '>' . esc_html(pll__('ALFABETYCZNIE')) . '</a></li>';
    $output .= '</ul>';
    $output .= '</div>';

    // Include a small script to handle sorting selection dynamically
    $output .= '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var sortOption = "' . $sort_option . '";
        var links = document.querySelectorAll(".sorting-container a");
        links.forEach(function(link) {
            if (link.href.includes("sort=" + sortOption)) {
                link.style.textDecoration = "none";
                link.style.pointerEvents = "none";
            }
        });
    });
    </script>';
    
    // Create an array to group events by year or for alphabetical sorting
    $events_by_year = [];
    
    // Loop through events and group by the year of the mec_start_date
    foreach ($events as $event_group) {
        foreach ($event_group as $event) {
            // Get the event start date from the meta field
            $start_date = isset($event->data->meta['mec_start_date']) ? $event->data->meta['mec_start_date'] : '';
            
            // Extract the year from the start date
            $year = date('Y', strtotime($start_date));
            
            // Group events by year if sorting by year
            if ($sort_option === 'year') {
                $events_by_year[$year][] = $event;
            } else {
                // Add events to a single array for alphabetical sorting
                $events_by_year['all'][] = $event;
            }
        }
    }

    // Sort alphabetically if the sorting option is 'alphabetical'
    if ($sort_option === 'alphabetical') {
        usort($events_by_year['all'], function($a, $b) {
            return strcmp($a->data->title, $b->data->title);
        });
    }

    // Render the events
    if (!empty($events_by_year)) {
        foreach ($events_by_year as $year => $events_in_year) {
            // Only show year heading if sorting by year
            if ($sort_option === 'year') {
                $output .= '<div class="year-divider" role="separator" aria-label="Wydarzenia z roku ' . esc_attr($year) . '">' . esc_html($year) . '</div>';
            }
            $output .= '<hr class="year-separator">';
            $output .= '<div class="events-group">';

           
            foreach ($events_in_year as $event) {
                // Extract event details
                $event_excerpt = isset($event->data->post->post_excerpt) ? esc_html($event->data->post->post_excerpt) : '';
                $event_title = isset($event->data->title) ? esc_html($event->data->title) : '';
                // $event_permalink = isset($event->data->post->guid) ? esc_url($event->data->post->guid) : '';
                $event_permalink = isset($event->data->ID) ? get_permalink($event->data->ID) : '';
                $event_start_date = isset($event->data->meta['mec_start_date']) ? esc_html($event->data->meta['mec_start_date']) : '';
                // event end date
                $event_end_date = isset($event->data->meta['mec_end_date']) ? esc_html($event->data->meta['mec_end_date']) : '';
                $event_thumbnail = isset($event->data->featured_image['large']) ? esc_url($event->data->featured_image['large']) : '';
                $formatted_start_date = date('d.m.Y', strtotime($event_start_date));
                $formatted_end_date = date('d.m.Y', strtotime($event_end_date));
            
                // Render each event as an article element
                $output .= '<article class="event-item" itemscope="">';
                $output .= '<a class="event-link" href="' . $event_permalink . '" target="_self" rel="noopener">';
            
                // Event image
                $output .= '<div class="event-image">';
                if ($event_thumbnail) {
                    $output .= '<img class="event-thumbnail" src="' . $event_thumbnail . '" alt="' . $event_title . '">';
                }
                $output .= '</div>';
            
                // Event content
                $output .= '<div class="event-details">';
                $output .= '<h3 class="event-title">' . $event_title . '</h3>';
                // excerpt
                $output .= '<p class="event-excerpt">' . $event_excerpt . '</p>';
                $output .= '</div>';
                
                // // Event date
                // $output .= '<div class="event-date-range">';
                // $output .= '<span class="event-dates">' . $formatted_start_date . ' - ' . $formatted_end_date . '</span>';
                // $output .= '</div>';
            
                
                $output .= '</a>'; // Close the event link
                $output .= '</article>';
            }
            $output .= '</div>'; // Close events-group
        }
    } else {
        // If no events are found, display a message
        $output .= '<p class="no-events-message">' . esc_html(pll__('Nie znaleziono wydarzeń.')) . '</p>';
    }

    $output .= '</div>'; // Close archive-events-container

    // Include a small script to handle sorting selection dynamically
    $output .= '<script>
    document.getElementById("event-sorting").addEventListener("change", function() {
        var selectedValue = this.value;
        var url = new URL(window.location.href);
        url.searchParams.set("sort", selectedValue);
        window.location.href = url.toString();
    });
    </script>';

    return $output;
}



function debug_mec_events($shortcode_id) {
    $events = MEC_main::get_shortcode_events($shortcode_id);
    return '<pre>' . print_r($events, true) . '</pre>';
}

function custom_mec_current_output($shortcode_id) {
    $events = MEC_main::get_shortcode_events($shortcode_id);
    
    // Initialize the output
    $output = '<div class="archive-events-container archive-events-container--current">';
    $output .= '<div class="events-group">';

    // Create an array to store unique events
    $unique_events = [];
    $seen_titles = [];

    // Loop through all events
    foreach ($events as $daily_events) {
        foreach ($daily_events as $event) {
            $title = $event->data->title;
            
            // Only add the event if we haven't seen this title before
            if (!isset($seen_titles[$title])) {
                $seen_titles[$title] = true;
                $unique_events[] = $event;
            }
        }
    }

    // Render each unique event
    foreach ($unique_events as $event) {
        // Extract event details
        $event_excerpt = isset($event->data->post->post_excerpt) ? esc_html($event->data->post->post_excerpt) : '';
        $event_title = isset($event->data->title) ? esc_html($event->data->title) : '';
        $event_permalink = isset($event->data->post->guid) ? esc_url($event->data->post->guid) : '';
        $event_thumbnail = isset($event->data->featured_image['large']) ? esc_url($event->data->featured_image['large']) : '';
        
        // Get start and end dates
        $start_date = isset($event->data->meta['mec_start_date']) ? $event->data->meta['mec_start_date'] : '';
        $end_date = isset($event->data->meta['mec_end_date']) ? $event->data->meta['mec_end_date'] : '';
        
        // Format dates
        $formatted_start_date = date('d.m.Y', strtotime($start_date));
        $formatted_end_date = date('d.m.Y', strtotime($end_date));
        
        // Render each event as an article element
        $output .= '<article class="event-item" itemscope="">';
        $output .= '<a class="event-link" href="' . $event_permalink . '" target="_self" rel="noopener">';
        
        // Event image
        $output .= '<div class="event-image">';
        if ($event_thumbnail) {
            $output .= '<img class="event-thumbnail" src="' . $event_thumbnail . '" alt="' . $event_title . '">';
        }
        $output .= '</div>';
        
        // Event content
        $output .= '<div class="event-details">';
        $output .= '<h3 class="event-title">' . $event_title . '</h3>';
        
        // Date range
        $output .= '<div class="event-date-range">';
        $output .= '<span class="event-dates">' . $formatted_start_date;
        if ($start_date !== $end_date) {
            $output .= ' - ' . $formatted_end_date;
        }
        $output .= '</span>';
        $output .= '</div>';
        
        $output .= '<p class="event-excerpt">' . $event_excerpt . '</p>';
        $output .= '</div>';
        
        $output .= '</a>'; // Close the event link
        $output .= '</article>';
    }

    $output .= '</div>'; // Close events-group
    $output .= '</div>'; // Close archive-events-container

    return $output;
}


add_shortcode('custom_mec_shortcode_2733', function () {
    return custom_mec_archival_output(2733);
});

add_shortcode('custom_mec_shortcode_2870', function() {
    return custom_mec_archival_output(2870);
});

add_shortcode('custom_mec_shortcode_12699', function() {
    return custom_mec_archival_output(12699);
});

add_shortcode('custom_mec_shortcode_2742', function() {
    return custom_mec_archival_output(2742);
});

add_shortcode('custom_mec_shortcode_3747', function() {
    return custom_mec_archival_output(3747);
});

// 3745
add_shortcode('debug_mec_events_3745', function() {
    // return debug_mec_events(3745);
    return custom_mec_current_output(3745);
});

// add_shortcode('custom_mec_shortcode_3036_homepage', function() {
//     return custom_mec_current_output(3036);
// });



function enqueue_custom_fullcalendar_script() {
    wp_enqueue_script('custom-fullcalendar-js', get_stylesheet_directory_uri() . '/js/fullcalendar-custom.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_fullcalendar_script');

?>