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
                $event_permalink = isset($event->data->post->guid) ? esc_url($event->data->post->guid) : '';
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


function renderDeeperTicketsView($selected_event_id) {
    // Optional Solution
    // Fetch only the specific event based on the selected_event_id 

    $single = new MEC_skin_single();
    $single_event_main = $single->get_event_mec($selected_event_id);
    $single_event_obj = $single_event_main[0];

    $events = MEC_main::get_shortcode_events(3827); 
    // Initialize the output
    $output = '<div class="bilety-container bilety-container--deeper">';

    // Display the selected event title
    $output .= '<a href="' . remove_query_arg('wybrane') . '" class="bilety-back-link">' . esc_html(pll__('← POWRÓT DO WYDARZEŃ')) . '</a>';
    $output .= '<p class="bilety-declaration remove-bottom-margin">' . esc_html(pll__('Dostępne bilety dla:')) . '</p>';
    $output .= '<h2 class="bilety-selected-title"><a href="' . get_permalink($selected_event_id) . '">' . get_the_title($selected_event_id) . '</a></h2>';
    // hr 
    $output .= '<hr class="bilety-hr">';


    // Render the events
    if (!empty($events)) {
        foreach ($events as $event_group) {
            foreach ($event_group as $event) {
                // Extract event details
                $event_id = esc_attr($event->data->ID);
                // Skip rendering this event if a specific event is selected and it doesn't match
                if ($selected_event_id && $selected_event_id !== $event_id) {
                    continue;
                }

                $event_title = isset($event->data->title) ? esc_html($event->data->title) : '';
                $event_permalink = isset($event->data->post->guid) ? esc_url($event->data->post->guid) : '';
                $event_start_date = isset($event->date['start']['date']) ? esc_html($event->date['start']['date']) : '';
                $formatted_start_date = date('d.m.Y', strtotime($event_start_date));

                // $data = (isset($event->data) and isset($event->data->meta) and isset($event->data->meta['mec_fields']) and is_array($event->data->meta['mec_fields'])) ? $event->data->meta['mec_fields'] : get_post_meta($event->ID, 'mec_fields', true);
                $mec_repeat_status = (isset($event->data->meta['mec_repeat_status'])) ? $event->data->meta['mec_repeat_status'] : null;
                $start_timestamp = (isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) ? $event->date['start']['timestamp'] : NULL;

                $is_free = (isset($event->data->meta['mec_fields_17'])) ? $event->data->meta['mec_fields_17'] : null;

                // if($start_timestamp) $data = MEC_feature_occurrences::param($event->ID, $start_timestamp, 'fields', $data);
                
                $metadata = MEC_feature_occurrences::param($event_id, $start_timestamp, '*');

                if ($mec_repeat_status == 0) {
                    $tickets_availability = (isset($event->data->meta['mec_fields_12'])) ? $event->data->meta['mec_fields_12'] : null;
                    $ticket_link = (isset($event->data->meta['mec_fields_5'])) ? $event->data->meta['mec_fields_5'] : null;
                    $is_premiere = (isset($event->data->meta['mec_fields_16'])) ? $event->data->meta['mec_fields_16'] : null;
                    $accessibility_features =  (isset($event->data->meta['mec_fields_7'])) ? $event->data->meta['mec_fields_7'] : null;
                    $location_id = get_post_meta($event_id, 'mec_location_id', true);
                    
                } else if ($mec_repeat_status == 1) {
                    $tickets_availability = isset($metadata['fields'][12]) ? $metadata['fields'][12] : null;
                    $ticket_link = isset($metadata['fields'][5]) ? $metadata['fields'][5] : null;
                    $is_premiere = isset($metadata['fields'][16]) ? $metadata['fields'][16] : null;
                    $accessibility_features = isset($metadata['fields'][7]) ? $metadata['fields'][7] : null;
                    // First check if there's a specific location for this occurrence
                    $location_id = isset($metadata['location_id']) ? $metadata['location_id'] : null;
                    // If no specific location is set, use the main event's location
                    if (empty($location_id)) {
                        $location_id = get_post_meta($event_id, 'mec_location_id', true);
                    }
                } else {
                    $tickets_availability = null;
                    $ticket_link = null;
                    $is_premiere = null;
                    $accessibility_features = null;
                }
                $is_sold_out = ($tickets_availability == 'wyprzedane') && $is_free !== 'tak';
                $sold_out_class = $is_sold_out  ? ' sold-out' : '';
                $strike_through_class = $is_sold_out ? ' sold-out--strike' : '';

                if (is_array($accessibility_features)) {
                    $accessibility_features = implode(', ', $accessibility_features);
                }

                // Extract custom fields
                $event_fields = isset($event->data->meta['mec_fields']) ? $event->data->meta['mec_fields'] : [];

                
                

                // Render each event as an article element
                $output .= '<article class="bilety-item" itemscope="">';

                // Event content
                $output .= '<div class="bilety-item-content">';
                // Display tags
                $event_tags = isset($event->data->tags) ? $event->data->tags : '';
                if ($event_tags) {
                    $output .= '<div class="event-tags">';
                    foreach ($event_tags as $tag) {
                        $output .= '<span class="event-tag">#' . esc_html($tag['name']) . ' </span>';
                    }
                    $output .= '</div>';
                }



                // Extract the start time from the event object
                $start_time = isset($event->data->time['start']) ? $event->data->time['start'] : '';
                $start_time_24h = date('H:i', strtotime($start_time));
                // if premiere is true, display premiere label
                $output .= '<div class="occurence-event-header">';
                if ($is_premiere === "tak") {
                    $output .= '<span class="premiere-label">premiera</span>';
                }
                $output .= '<p class="bilety-item-date">' . $formatted_start_date . ', ' . $start_time_24h . '</p>';
                $output .= '</div>';
                // accessibility features
                // if there are accessibility features, display them
                if ($location_id) {
                    $location = get_term($location_id, 'mec_location');
                    if (!is_wp_error($location) && !empty($location)) {
                        // Get location address from term meta instead of URL
                        $location_address = get_term_meta($location_id, 'address', true);
                        $location_url = !empty($location_address) ? 'https://maps.google.com/?q=' . urlencode($location_address) : '';
                        
                        $output .= '<p class="occurrence-event-location ">';
                        $output .= '<img src="' . esc_url(home_url('/wp-content/uploads/2024/10/location.svg')) . '" 
                                  alt="" 
                                  aria-hidden="true" 
                                  width="21" 
                                  height="20">';
                        $output .= '<span class="visually-hidden">Lokalizacja: </span>';
                        
                        if (!empty($location_url)) {
                            $output .= '<a href="' . esc_url($location_url) . '" target="_blank" rel="noopener noreferrer">';
                            $output .= '<span>' . esc_html($location->name) . '</span>';
                            $output .= '</a>';
                        } else {
                            $output .= '<span>' . esc_html($location->name) . '</span>';
                        }
                        
                        $output .= '</p>';
                    }
                }
                if ($accessibility_features) {
                    $output .= '<p class="bilety-item-accessibility">+ ' . $accessibility_features . '</p>';
                }

                $output .= '</div>';

                // Event footer with booking button
                $output .= '<div class="bilety-footer">';
                
                // if is free equals tak, display info that it's free 
                // if is sold out and isnt free, display sold out
                // if its not free and not sold out display buy ticket link
                if ($is_free === 'tak') {
                    $output .= '<span class="uppercase">' . esc_html(pll__('darmowe')) . '</span>';
                } else if ($is_sold_out && $is_free !== 'tak') {
                    $output .= '<span class="uppercase">' . esc_html(pll__('wyprzedane')) . '</span>';
                } else if (isset($ticket_link) && !empty($ticket_link) && !$is_sold_out && $is_free !== 'tak') {
                    $output .= '<a href="' . esc_url($ticket_link) . '" class="buy-ticket-button" target="_blank" rel="noopener noreferrer" aria-label="Kup bilet na wydarzenie ' . esc_attr($event_title) . ' w dniu ' . esc_attr($formatted_start_date) . '">';
                    $output .= esc_html(pll__('Kup bilet'));
                    $output .= '<div class="svg-container">';
                    $output .= '<div class="svg-one"><div class="svg-wrapper">';
                    $output .= file_get_contents(get_stylesheet_directory() . '/assets/images/bilet.svg');
                    $output .= '</div></div>';
                    $output .= '<div class="svg-two"><div class="svg-wrapper">';
                    $output .= file_get_contents(get_stylesheet_directory() . '/assets/images/bilet.svg');
                    $output .= '</div></div>';
                    $output .= '</div>';
                    $output .= '</a>';
                } 

                $output .= '</div>';

                $output .= '</article>';
            }

        }
    } else {
        // If no events are found, display a message
        $output .= '<p class="bilety-no-events">' . __('No events found.', 'astra-child') . '</p>';
    }

    $output .= '</div>';

    return $output;
}

function renderTicketViewForSelectedEvent($selected_event_id) {
    $single = new MEC_skin_single();
    $single_event_main = $single->get_event_mec($selected_event_id);
    $single_event_obj = $single_event_main[0];


    return;

}

function custom_mec_tickets_output($shortcode_id) {
    // Fetch events for the given shortcode ID
    $events = MEC_main::get_shortcode_events($shortcode_id);

    // Check if the 'wybrane' parameter is present in the URL
    $selected_event_id = isset($_GET['wybrane']) ? $_GET['wybrane'] : null;

    // Initialize the output
    $output = '<div class="bilety-container">';

    // if wybrane is set, call renderDeeperTicketsView function
    if ($selected_event_id && $selected_event_id !== null) {
        return renderDeeperTicketsView($selected_event_id);
    }

    // Render the events
    if (!empty($events)) {
        foreach ($events as $event_group) {
            foreach ($event_group as $event) {
                


                // Extract event details
                $event_id = esc_attr($event->data->ID);
                // event excerpt is in data: post_excerpt
                $event_excerpt = isset($event->data->post->post_excerpt) ? esc_html($event->data->post->post_excerpt) : '';
                $event_title = isset($event->data->title) ? esc_html($event->data->title) : '';
                $event_permalink = isset($event->data->post->guid) ? esc_url($event->data->post->guid) : '';
                $event_start_date = isset($event->data->meta['mec_start_date']) ? esc_html($event->data->meta['mec_start_date']) : '';
                $event_end_date = isset($event->data->meta['mec_end_date']) ? esc_html($event->data->meta['mec_end_date']) : '';
                $event_thumbnail = isset($event->data->featured_image['thumblist']) ? esc_url($event->data->featured_image['thumblist']) : '';
                $formatted_start_date = date('d.m.Y', strtotime($event_start_date));
                $formatted_end_date = date('d.m.Y', strtotime($event_end_date));

                // Extract custom fields
                $event_fields = isset($event->data->meta['mec_fields']) ? $event->data->meta['mec_fields'] : [];

                // Skip rendering this event if a specific event is selected and it doesn't match
                if ($selected_event_id && $selected_event_id !== $event_id) {
                    continue;
                }

                // Render each event as an article element
                $output .= '<article class="bilety-item" itemscope="">';

                // Event content
                $output .= '<div class="bilety-item-content">';
                // Display tags
                $event_tags = isset($event->data->tags) ? $event->data->tags : '';
                if ($event_tags) {
                    $output .= '<div class="event-tags">';
                    foreach ($event_tags as $tag) {
                        $output .= '<span class="event-tag">#' . esc_html($tag['name']) . ' </span>';
                    }
                    $output .= '</div>';
                }
                $output .= '<h4 class="bilety-item-title"><a class="color-hover" data-event-id="' . $event_id . '" href="' . $event_permalink . '" target="_self" rel="noopener">' . $event_title . '</a></h4>';
                // event excerpt
                $output .= '<p class="bilety-item-excerpt">' . $event_excerpt . '</p>';
  

                $output .= '</div>';

                // Event footer with booking button
                $output .= '<div class="bilety-footer">';
                
                $output .= '<a class="bilety-item-link" data-event-id="' . $event_id . '" href="?wybrane=' . $event_id . '" target="_self" rel="noopener">' . esc_html(pll__('Wybierz datę')) . '</a>';
                $output .= '</div>';

                $output .= '</article>';
            }
        }
    } else {
        // If no events are found, display a message
        $output .= '<p>' . __('No events found.', 'astra-child') . '</p>';
    }

    $output .= '</div>';

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

add_shortcode('custom_mec_tickets_shortcode_2', function() {
    return custom_mec_tickets_output(3812);
});

function enqueue_custom_fullcalendar_script() {
    wp_enqueue_script('custom-fullcalendar-js', get_stylesheet_directory_uri() . '/js/fullcalendar-custom.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_fullcalendar_script');

?>