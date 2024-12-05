<?php

function get_event_field_value_global($event, $field_number) {
    global $map_events;
    
    // Store the first occurrence of each event ID
    if (!isset($map_events[$event->data->ID])) {
        $map_events[$event->data->ID] = $event;
    }
    
    $first_occurrence = $map_events[$event->data->ID];
    
    // Check for edited occurrences for the current event's specific date
    $current_date = $event->date['start']['date'];
    return $first_occurrence->data->edited_occurrences[$current_date]['fields'][$field_number] ?? 
           $first_occurrence->data->meta['mec_fields'][$field_number] ?? 
           '';
}


function render_event_location($location) {
    if(!empty($location) && !empty($location['name'])): ?>
        <div class="calendar-item-location">
            <img src="<?php echo esc_url(home_url('/wp-content/uploads/2024/10/location.svg')); ?>" 
                alt="" 
                aria-hidden="true" 
                width="21" 
                height="20">
            <span class="visually-hidden">Lokalizacja: </span>
            <?php 
            $locationUrl = !empty($location['address']) ? 'https://maps.google.com/?q=' . urlencode($location['address']) : '';
            if (!empty($locationUrl)) {
                echo '<a href="' . esc_url($locationUrl) . '" target="_blank" rel="noopener noreferrer">';
                echo '<span>' . esc_html($location['name']) . '</span>';
                echo '</a>';
            } else {
                echo '<span>' . esc_html($location['name']) . '</span>';
            }
            
            ?>
        </div>
    <?php endif;
}