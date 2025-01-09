<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_masonry $this */

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

if ( ! function_exists( 'custom_get_last_future_date' ) ) {
    function custom_get_last_future_date($event, $event_start_date) {
        if ($event->data->meta['mec_repeat_status'] != 1) {
            return '';
        }

        $days = $event->data->mec->days;
        $start_date = $event->data->mec->start;
        $end_date = $event->data->mec->end;

        if (!empty($days)) {
            $dates = array_map(
                function($date_entry) {
                    return substr($date_entry, 0, strpos($date_entry, ':'));
                },
                explode(',', $days)
            );

            sort($dates);
            $last_date = end($dates);

            return ($last_date !== $event_start_date) ? $last_date : '';
        }

        return ($start_date !== $end_date && $end_date !== $event_start_date) ? $end_date : '';
    }
}


// dubug events:
// echo '<pre>';
// print_r($this->events);
// echo '</pre>';
?>

<div class="mec-wrap custom-mec-container">
    <div class="custom-grid-layout" role="region"> 

        <?php
        foreach($this->events as $date):
        foreach($date as $event):
            $location_id = $this->main->get_master_location_id($event);
            $location = ($location_id ? $this->main->get_location_data($location_id) : array());
            $organizer_id = $this->main->get_master_organizer_id($event);
            $organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());
            $event_color = $this->get_event_color_dot($event);
            $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
            $event_title = get_the_title($event->data->ID);
            $mec_repeat_status = $event->data->meta['mec_repeat_status'];

            $last_future_date = custom_get_last_future_date($event, $event_start_date);

            // MEC Schema
            do_action('mec_schema', $event);
            ?>
            <a href="<?php echo esc_url($this->main->get_event_date_permalink($event, $event->date['start']['date'])); ?>" 
               class="custom-grid-item <?php echo esc_attr($this->filter_by_classes($event->data->ID)); ?>"

               aria-label="<?php echo esc_attr($event_title . ', ' . $this->main->date_i18n($this->date_format_1, strtotime($event->date['start']['date']))); ?>"
            >
                 
                <?php if(isset($event->data->featured_image)): ?>
                    <div class="custom-grid-item-image-container">
                        <?php 
                        $image_id = get_post_thumbnail_id($event->data->ID);
                        $image_url = wp_get_attachment_image_url($image_id, 'large');
                        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                        if ($image_url) {
                            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" class="custom-grid-item-image">';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="custom-grid-item-content">
                    <div class="custom-grid-item-title" id="event-title-<?php echo esc_attr($event->data->ID); ?>">
                        <?php echo esc_html($event_title); ?>
                    </div>
                    <div class="custom-grid-item-description">
                        <div class="custom-mec-event-date" id="event-date-<?php echo esc_attr($event->data->ID); ?>">
                            <?php echo esc_html($this->main->date_i18n($this->date_format_1, strtotime($event->date['start']['date']))); ?> 
                            <!-- if last_future_date not empty, show it -->
                            <?php if (!empty($last_future_date)): ?>
                               - <?php echo esc_html($this->main->date_i18n($this->date_format_1, strtotime($last_future_date))); ?>
                            <?php endif; ?>
                        </div>
                        <div class="custom-mec-event-labels">
                            <?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?>
                        </div>
                        <div class="custom-mec-event-categories">
                            <?php echo MEC_kses::element($this->display_categories($event)); ?>
                        </div>
                        <div class="custom-mec-event-organizers">
                            <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                        </div>
                        <div class="custom-mec-event-excerpt">
                            <div><?php echo MEC_kses::element(get_the_excerpt($event->data->post)); ?></div>
                        </div>
                        <?php if(isset($location['name']) and trim($location['name'])): ?>
                                <div class="mec-masonry-col6">
                                    <div class="mec-event-location">
                                    <img src="<?php echo esc_url(home_url('/wp-content/uploads/2024/10/location.svg')); ?>" 
                alt="" 
                aria-hidden="true" 
                width="21" 
                height="20">
                                        <div class="mec-event-location-det">
                                            <span class="mec-location-name"><?php echo esc_html($location['name']); ?></span>
                                           
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                       
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
