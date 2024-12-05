<?php

global $map_events;
$map_events = [];

function get_event_location_global($event) {
    global $map_events;
    
    // Store the first occurrence of each event ID
    if (!isset($map_events[$event->data->ID])) {
        $map_events[$event->data->ID] = $event;
    }
    
    $first_occurrence = $map_events[$event->data->ID];
    
    // Check for edited occurrences for the current event's specific date
    $current_date = $event->date['start']['date'];

    return $first_occurrence->data->edited_occurrences[$current_date]['location'] ?? 
           $first_occurrence->data->meta['mec_location'] ?? 
           [];
}

function render_tickets_view($events) {
    // debug events
    // print the number of events
    // echo count($events);
    // return '<pre>' . print_r($events, true) . '</pre>';
    ob_start();
    ?>
    <div class="bilety-container">
        <?php foreach ($events as $date => $daily_events): ?>
            <?php foreach ($daily_events as $event): ?>
                <?php
                    $dateTimestamp = strtotime($date);
                    $formattedDate = date_i18n('d.m.Y', $dateTimestamp);
                    $timeOfEvent = date('H:i', strtotime($event->data->time['start'] ?? ''));
                    $title = $event->data->title ?? '';
                    $permalink = $event->data->permalink ?? '';
                    $excerpt = $event->data->post->post_excerpt ?? '';
                    $accessibility_features = get_event_field_value_global($event, 7);

                    if (is_array($accessibility_features)) {
                        $accessibility_features = implode(', ', $accessibility_features);
                    }
                    $location = get_event_location_global($event);
                    
                ?>
                <div class="bilety-item">
                    <div class="event-content">
                        <span class="event-time"><?php echo $formattedDate; ?>, <?php echo $timeOfEvent; ?></span>
                        <h4 class="event-title">
                            <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                        </h4>
                        
                        <?php if (!empty($excerpt)): ?>
                            <div class="event-excerpt"><?php echo wp_kses_post($excerpt); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($accessibility_features)): ?>
                                <div class=" event-item-accessibility">+ <?php echo esc_html($accessibility_features); ?></div>
                        <?php endif; ?>
                       <!-- if location is not empty -->
                       <?php if (!empty($location)): ?>
                            <?php render_event_location($location); ?>
                       <?php endif; ?>
                    </div>
                    <div class="bilety-footer">
                        Bileciki
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

function custom_mec_tickets_page ($shortcode_id) {

    $events = MEC_main::get_shortcode_events($shortcode_id);

    if (empty($events)) {
        return '<p>' . __('Brak wydarzeń biletowanych.', 'astra-child') . '</p>';
    }
    return render_tickets_view($events);

}


add_shortcode('custom_mec_tickets_shortcode_2', function() {
    return custom_mec_tickets_page(3812);
});