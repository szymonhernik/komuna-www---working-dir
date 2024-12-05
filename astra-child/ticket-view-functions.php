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
                    $tickets_available_value = get_event_field_value_global($event, 12);
                    $tickets_soldout = $tickets_available_value === 'wyprzedane' ? 'wyprzedane' : 'dostępne';
                    $sold_out_class = $tickets_soldout === 'wyprzedane' ? ' sold-out' : '';
                    $ticket_link = get_event_field_value_global($event, 5);
                    // debug ticket link
                    // echo '<pre>';
                    // var_dump($ticket_link);
                    // echo '</pre>';
    
                    // is event free 
                    $is_event_free = get_event_field_value_global($event, 17);
    
                    // Skip rendering if the event is free or tickets are available with a link
                    if ($is_event_free === 'tak' || ($tickets_soldout === 'dostępne' && empty($ticket_link))) {
                        continue;
                    }
    
                    // ticket div button element
                    // if event is free show "wstęp wolny" (no link)
                    // if event is not free and tickets are not sold out show "bilety" (it should be a link to bilety page)
                    // if event is not free and tickets are sold out show "wyprzedane" (no link)
                    // ticket html element
                    $ticket_html = '';
                    if($is_event_free === 'tak') {
                        $ticket_html = '<span class="free-entry">wstęp wolny</span>';
                    }
                    else if($tickets_soldout === 'dostępne' && !empty($ticket_link)) {
                        $ticket_html = '<a href="' . $ticket_link . '" class="buy-ticket-button calendar-item-ticket">bilety</a>';
                    }
                    else if($tickets_soldout === 'wyprzedane') {
                        $ticket_html = '<span class="sold-out">wyprzedane</span>';
                    } 
                    
                ?>
                <div class="bilety-item">
                    <div class="event-content <?php echo $sold_out_class; ?>">
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
                        <?php echo $ticket_html; ?>
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