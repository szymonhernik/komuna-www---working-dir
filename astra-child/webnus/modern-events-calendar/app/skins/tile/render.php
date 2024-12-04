<?php

/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_tile $this */


$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

$method = isset($this->skin_options['sed_method']) ? $this->skin_options['sed_method'] : false;
$map_events = [];

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
            } 
            echo '<span>' . esc_html($location['name']) . '</span>';
            
            ?>
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
<div class="custom-mec-wrap <?php echo esc_attr($event_colorskin); ?>">
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

                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                // Convert start time to 24-hour format
                $start_time_24h = date('H:i', strtotime($start_time));
                
                // Check for all-day event
                $is_all_day = isset($event->data->meta['mec_allday']) && $event->data->meta['mec_allday'] == '1';

                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                $event_color = $this->get_event_color_dot($event, true);
                $background_image = (isset($event->data->featured_image['tileview']) && trim($event->data->featured_image['tileview'])) ? ' url(\''.trim($event->data->featured_image['tileview']).'\')' : '';
                
          
                
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
                
                $premiere_status = get_event_field_value($event, 16);

                
          

                
                $custom_data_class = !empty($mec_data) ? 'mec-custom-data' : '';
                $accessibility_features = get_event_field_value($event, 7);
                if (is_array($accessibility_features)) {
                    $accessibility_features = implode(', ', $accessibility_features);
                }
          

                // Multiple Day Event Class
                $me_class = $event_start_date == $event->date['end']['date'] || (isset($this->settings['multiple_day_show_method']) && $this->settings['multiple_day_show_method'] == 'all_days') ? '' : 'tile-multipleday-event';
                
                // MEC Schema
                do_action('mec_schema', $event);
                ?>
     

                
                    <article class="<?php echo ((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : ''); ?> calendar-item  <?php echo esc_attr($me_class); ?> mec-clear <?php echo esc_attr($this->get_event_classes($event)); ?> <?php echo esc_attr($custom_data_class); ?>">
                        <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                    
                        <div class="calendar-item-content">
                            <!-- Image Column -->
                            <?php render_event_image($event, get_event_banner_image($event, $banner), $width, $height); ?>
                            <!-- Title, Description, and Accessibility Column -->
                            <div class=" calendar-item-details">
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
                        <div class="column-time-and-ticket calendar-item-meta">
                            <div class="calendar-item-time">
                            <?php echo $is_all_day ? (function_exists('pll__') ? pll__('Cały dzień') : esc_html__('all day', 'modern-events-calendar-lite')) : esc_html($start_time_24h); ?>
                            </div>
                            <a href="<?php echo esc_url(home_url('/bilety/?wybrane=' . $event->data->ID)); ?>" class="buy-ticket-button calendar-item-ticket">Bilety</a>
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