<?php

/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_tile $this */


$styling = $this->main->get_styling();

$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

$method = isset($this->skin_options['sed_method']) ? $this->skin_options['sed_method'] : false;
$map_events = [];

// function get_event_field_value_global($event, $field_number) {
//     global $map_events;
    
//     // Store the first occurrence of each event ID
//     if (!isset($map_events[$event->data->ID])) {
//         $map_events[$event->data->ID] = $event;
//     }
    
//     $first_occurrence = $map_events[$event->data->ID];
    
//     // Check for edited occurrences for the current event's specific date
//     $current_date = $event->date['start']['date'];
//     return $first_occurrence->data->edited_occurrences[$current_date]['fields'][$field_number] ?? 
//            $first_occurrence->data->meta['mec_fields'][$field_number] ?? 
//            '';
// }


// New helper functions at the top of the file
function group_events_by_date($events) {
    $groupedEvents = array();
    foreach($events as $date => $events) {
        $dateTimestamp = strtotime($date);
        $formattedDate = date_i18n('d.m', $dateTimestamp);
        $dayOfWeek = date_i18n('l', $dateTimestamp);
        $formattedDateWithDay = $formattedDate . ' <span class="mec-day-name">' . $dayOfWeek . '</span>';
        $groupedEvents[$formattedDateWithDay] = $events;
    }
    return $groupedEvents;
}

function render_event_image($event, $image, $width = '', $height = '') {
    if(isset($event->data->featured_image['tileview']) && trim($event->data->featured_image['tileview'])): ?>
        <div class="calendar-item-image">
            <img src="<?php echo esc_url($image); ?>" 
                 alt="<?php echo esc_attr($event->data->title); ?>" 
                 width="<?php echo esc_attr($width); ?>" 
                 height="<?php echo esc_attr($height); ?>"
                 loading="lazy">
        </div>
    <?php endif;
}



function get_event_banner_image($event, $banner) {
    $image = $banner['image'] ?? '';
    $featuredImage = $banner['use_featured_image'] ?? 0;
    
    if($featuredImage || empty($image)) {
        return $event->data->featured_image['medium_large'] ?? 
               $event->data->featured_image['large'] ?? 
               $event->data->featured_image['medium'] ?? '';
    }
    
    if (!$featuredImage && !empty($image)) {
        $imageId = attachment_url_to_postid($image);
        if ($imageId) {
            $imageArray = wp_get_attachment_image_src($imageId, 'medium_large');
            return $imageArray ? $imageArray[0] : $image;
        }
    }
    
    return $image;
}

// Main render code
?>


<style>
  
</style>
<div class="custom-mec-wrap">
    <div class="mec-event-tile-view custom-tile-view">
        <?php
        $count = $this->count;
        if($count == 0 or $count == 5) $col = 4;
        else $col = 12 / $count;

        // debug events
        // echo '<pre>';
        // var_dump($this->events);
        // echo '</pre>';
        

        $grouped_events = group_events_by_date($this->events);
        
        foreach($grouped_events as $formatted_date_with_day => $events):
            ?>
            <h3 class="custom-date-header"><?php echo wp_kses_post($formatted_date_with_day); ?></h3>
            <hr>
            <div class="row custom-row-calendar">
            <?php
            foreach($events as $event):
                $location_id = $this->main->get_master_location_id($event);
                $location = ($location_id ? $this->main->get_location_data($location_id) : array());
                // debug event 
                // echo '<pre>';
                // var_dump($event);
                // echo '</pre>';

                // start time 
               
                $display_time = get_formatted_event_time($event);
                // Check for all-day event
                $is_all_day = isset($event->data->meta['mec_allday']) && $event->data->meta['mec_allday'] == '1';

                

                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                $background_image = (isset($event->data->featured_image['tileview']) && trim($event->data->featured_image['tileview'])) ? ' url(\''.trim($event->data->featured_image['tileview']).'\')' : '';
                
                // get the tickets value from fields[12]
                // $tickets_available_value = get_event_field_value($event, 12);
                // $tickets_soldout = $tickets_available_value === 'wyprzedane' ? 'wyprzedane' : 'dostępne';
                

                $tickets_available_value = get_event_field_value_global($event, 12);
                $tickets_soldout = $tickets_available_value === 'wyprzedane' ? 'wyprzedane' : 'dostępne';
                $sold_out_class = $tickets_soldout === 'wyprzedane' ? ' sold-out' : '';

                // is event free 
                $is_event_free = get_event_field_value_global($event, 17);
                // ticket's link 
                $ticket_link = get_event_field_value_global($event, 5);


                // ticket div button element
                // if event is free show "wstęp wolny" (no link)
                // if event is not free and tickets are not sold out show "bilety" (it should be a link to bilety page)
                // if event is not free and tickets are sold out show "wyprzedane" (no link)
                // ticket html element
                $ticket_html = '';
                // Check if event is in the past
                $event_date = strtotime($event->date['start']['date']);
                $today = strtotime('today');
                if ($event_date >= $today) {  // Only show ticket info for current and future events
                    if($is_event_free === 'tak') {
                        $ticket_html = '<span class="free-entry">wstęp wolny</span>';
                    }
                    else if($tickets_soldout === 'dostępne' && !empty($ticket_link)) {
                        $ticket_html = '<a href="' . esc_url(home_url('/bilety')) . '" class="buy-ticket-button calendar-item-ticket">' . esc_html(pll__('bilety')) . '</a>';
                    }
                    else if($tickets_soldout === 'wyprzedane') {
                        $ticket_html = '<span class="sold-out">wyprzedane</span>';
                    }
                }
                
                
                $mec_data = $this->display_custom_data($event);
                $banner = isset($event->data, $event->data->meta, $event->data->meta['mec_banner']) ? $event->data->meta['mec_banner'] : [];
                if(!is_array($banner)) $banner = [];
                
                $image = $banner['image'] ?? '';
                $featured_image = $banner['use_featured_image'] ?? 0;
                 // Force Featured Image
                if(isset($this->settings['banner_force_featured_image']) && $this->settings['banner_force_featured_image'])
                {
                    $featured_image = 1;
                    if(trim($color) === '') $color = '#333333';
                }
                // if featured image is true and banner isnt empty
                if($featured_image || empty($image)) {
                    // Try to get medium_large size first, with fallbacks
                    $image = $event->data->featured_image['medium_large'] ?? $event->data->featured_image['large'] ?? $event->data->featured_image['medium'] ?? '';
                }
                // if we use banner and not featured image i'll want to get the larger version of the banner
                if (!$featured_image && !empty($image)) {
                    $image_id = attachment_url_to_postid($image);
                    if ($image_id) {
                        // Use medium_large size for a balanced resolution
                        $image_array = wp_get_attachment_image_src($image_id, 'medium_large');
                        if ($image_array) {
                            $image = $image_array[0];
                            $width = $image_array[1];
                            $height = $image_array[2];
                        }
                    }
                }
                
                $premiere_status = get_event_field_value_global($event, 16);

                
                // premiere status debug
                // echo '<pre>';
                // var_dump($premiere_status);
                // echo '</pre>';


                
                $custom_data_class = !empty($mec_data) ? 'mec-custom-data' : '';
                // accessibility features
                // $accessibility_features = get_event_field_value($event, 7);
                $accessibility_features = get_event_field_value_global($event, 7);
                if (is_array($accessibility_features)) {
                    $accessibility_features = implode(', ', $accessibility_features);
                }
                // debug accessibility features
                // echo '<pre>';
                // var_dump($accessibility_features);
                // echo '</pre>';
          
                
                // MEC Schema
                do_action('mec_schema', $event);
                ?>
     

                
                    <article class="<?php echo ((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : ''); ?> calendar-item   mec-clear <?php echo esc_attr($this->get_event_classes($event)); ?> <?php echo esc_attr($custom_data_class); ?>">
                        <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                    
                        <div class="calendar-item-content">
                            <!-- Image Column -->
                            <?php render_event_image($event, get_event_banner_image($event, $banner), $width, $height); ?>
                            <!-- Title, Description, and Accessibility Column -->
                            <div class=" calendar-item-details <?php echo $sold_out_class; ?>">
                            <?php if ($premiere_status !== null && $premiere_status === 'tak') {
                                // Show premiere badge or handle premiere status
                                echo '<div class="premiere-badge">Premiera</div>';
                            } ?>


                            <h4 class="calendar-item-title">
                                <a href="<?php echo esc_url($event->data->permalink); ?>"><?php echo esc_html($event->data->title); ?></a>
                            </h4>
                            <!-- Add event excerpt -->
                            <?php
                            $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';
                            if (empty($excerpt)) {
                                $excerpt = $event->data->post->post_content;
                            }
                            if (!empty($excerpt)) {
                                echo '<div class=" calendar-item-description">' . wp_kses_post($excerpt) . '</div>';
                            }
                            ?>
                            <!-- Locations -->
                            <?php render_event_location($location); ?>
                            
                            
                            <?php if (!empty($accessibility_features)): ?>
                                <div class=" calendar-item-accessibility">+ <?php echo esc_html($accessibility_features); ?></div>
                            <?php endif; ?>

                            </div>
                        </div>
                        <!-- Time and Ticket Link Column -->
                        <div class="column-time-and-ticket calendar-item-meta <?php echo $sold_out_class; ?>">
                            <div class="calendar-item-time">
                            <!-- this will have to be changed -->
                            <?php echo $display_time; ?>
                            </div>
                            <?php echo $ticket_html; ?>
                        </div>

                    </article>
               
            <?php
            endforeach;
            ?>
            </div>
        <?php
        endforeach;
        ?>
    </div>
</div>
