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

?>


<style>
  
</style>
<div class="custom-mec-wrap <?php echo esc_attr($event_colorskin); ?>">
    <div class="mec-event-tile-view custom-tile-view">
        <?php
        $count = $this->count;
        if($count == 0 or $count == 5) $col = 4;
        else $col = 12 / $count;

        // Group events by date
        $grouped_events = array();
        foreach($this->events as $date => $events):
            $date_timestamp = strtotime($date);
            $formatted_date = date_i18n('d.m', $date_timestamp);
            $day_of_week = date_i18n('l', $date_timestamp); // Get the full name of the day
            $formatted_date_with_day = $formatted_date . ' <span class="mec-day-name">' . $day_of_week . '</span>';
            $grouped_events[$formatted_date_with_day] = $events;
        endforeach;

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
                if($featured_image || empty($image)) $image = $event->data->featured_image['medium'] ?? '';
                // if we use banner and not featured image i'll want to get the small res version of the banner.
                if (!$featured_image && !empty($image)) {
                    $image_id = attachment_url_to_postid($image);
                    if ($image_id) {
                        // If we found an attachment ID, use WordPress's medium size
                        $image_array = wp_get_attachment_image_src($image_id, 'medium');
                        if ($image_array) {
                            $image = $image_array[0];
                            $width = $image_array[1];
                            $height = $image_array[2];
                        }
                    } else {
                        // If we couldn't find an attachment ID, fall back to the original URL
                        $image = $image;
                        $width = 300;  // Default width
                        $height = 200; // Default height
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
                <!-- echo $event->data  in html commented -->
                <!-- <?php 
                // echo '<pre style="background-color: #f5f5f5; padding: 10px; margin: 10px 0; overflow: auto; max-height: 400px;">';
                // echo '<h4>Event Debug Information:</h4>';
                // var_dump($event);
                // echo '</pre>';
                ?> -->

                
                    <article class="<?php echo ((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : ''); ?> calendar-item  <?php echo esc_attr($me_class); ?> mec-clear <?php echo esc_attr($this->get_event_classes($event)); ?> <?php echo esc_attr($custom_data_class); ?>">
                        <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                    
                        <?php if(isset($event->data->featured_image['tileview']) && trim($event->data->featured_image['tileview'])): ?>
                            <!-- <div className="md:col-span-1"> -->
                            <!-- Image Column -->
                            <div class="calendar-item-image">
                                <?php
                                
                                ?>
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($event->data->title); ?>" width="<?php echo esc_attr($width); ?>" height="<?php echo esc_attr($height); ?>">
                            </div>
                        <?php endif; ?>
                        <!-- Title, Description, and Accessibility Column       -->
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
                            <?php if(!empty($location) && !empty($location['name'])): ?>
                                <div class="calendar-item-location">
                                    <img src="<?php echo esc_url(home_url('/wp-content/uploads/2024/10/location.svg')); ?>" 
                                         alt="" 
                                         aria-hidden="true" 
                                         width="21" 
                                         height="20">
                                    <span class="visually-hidden">Lokalizacja: </span>
                                    <?php 
                                    $location_url = !empty($location['address']) ? 'https://maps.google.com/?q=' . urlencode($location['address']) : '';
                                    if (!empty($location_url)) {
                                        echo '<a href="' . esc_url($location_url) . '" target="_blank" rel="noopener noreferrer">';
                                        echo '<span>' . esc_html($location['name']) . '</span>';
                                        echo '</a>';
                                    } else {
                                        echo '<span>' . esc_html($location['name']) . '</span>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                              
                             
                            <?php if (!empty($accessibility_features)): ?>
                                <div class=" calendar-item-accessibility">+ <?php echo esc_html($accessibility_features); ?></div>
                            <?php endif; ?>
                           
                        </div>
                        <!-- Time and Ticket Link Column -->
                        <div class="column-time-and-ticket calendar-item-meta">
                            <div class="calendar-item-time">
                                <?php echo esc_html($start_time_24h); ?>
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
