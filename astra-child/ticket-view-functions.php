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
                    $fields = get_robust_event_fields_values_global($event, [7, 12, 5, 17, 14]);
                    $accessibility_features = $fields[7];
                    $tickets_available_value = $fields[12];
                    $ticket_link = $fields[5];
                    $is_event_free = $fields[17];
                    $ticket_price_info = $fields[14];

                    if (is_array($accessibility_features)) {
                        $accessibility_features = implode(', ', $accessibility_features);
                    }
                    $location = get_event_location_global($event);
                    $tickets_soldout = $tickets_available_value === 'wyprzedane' ? 'wyprzedane' : 'dostępne';
                    $sold_out_class = $tickets_soldout === 'wyprzedane' ? ' sold-out' : '';
    
                    // Skip rendering if the event is free or tickets are available with a link
                    if ($is_event_free === 'tak' || ($tickets_soldout === 'dostępne' && empty($ticket_link))) {
                        continue;
                    }
    
                    $ticket_html = '';
                    if($is_event_free === 'tak') {
                        $ticket_html = '<span class="free-entry">' . esc_html(pll__('wstęp wolny')) . '</span>';
                    }
                    else if($tickets_soldout === 'dostępne' && !empty($ticket_link)) {
                        $ticket_html = '<a href="' . $ticket_link . '" class="buy-ticket-button calendar-item-ticket" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr(pll__('Kup bilet')) . ' ' . esc_attr($title) . ' ' . esc_attr($formattedDate) . '">';
                        $ticket_html .= esc_html(pll__('Kup bilet'));
                        $ticket_html .= '<div class="svg-container">';
                        $ticket_html .= '<div class="svg-one">';
                        $ticket_html .= '<div class="svg-wrapper">';
                        ob_start();
                        include(get_stylesheet_directory() . '/assets/images/bilet.svg');
                        $ticket_html .= ob_get_clean();
                        $ticket_html .= '</div>';
                        $ticket_html .= '</div>';
                        
                        $ticket_html .= '<div class="svg-two">';
                        $ticket_html .= '<div class="svg-wrapper">';
                        ob_start();
                        include(get_stylesheet_directory() . '/assets/images/bilet.svg');
                        $ticket_html .= ob_get_clean();
                        $ticket_html .= '</div>';
                        $ticket_html .= '</div>';
                        $ticket_html .= '</div>';
                        $ticket_html .= '</a>';
                    }
                    else if($tickets_soldout === 'wyprzedane') {
                        $ticket_html = '<span class="sold-out">' . esc_html(pll__('wyprzedane')) . '</span>';
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

                        <?php if (!empty($ticket_price_info)): ?>
                            <div class="event-item-ticket-price-info"><?php pll_e('Cena: '); ?> <?php echo esc_html($ticket_price_info); ?></div>
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