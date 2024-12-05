<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * The Template for displaying all single events
 * 
 * @author Webnus <info@webnus.net>
 * @package MEC/Templates
 * @version 1.0.0
 */
get_header('mec'); ?>

    <div id="primary">
    <section id="<?php echo apply_filters('mec_single_page_html_id', 'main-content'); ?>" class="<?php echo apply_filters('mec_single_page_html_class', 'iszszi'); ?>" style="max-width: 1488px; margin-left: auto; margin-right: auto;">
        <div  class="custom-single-event-wrapper">
            <?php do_action('mec_before_main_content'); ?>
            <?php
            // Add Yoast Breadcrumbs 
            if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<div class="breadcrumbs">', '</div>');
            }
            ?>



            <?php while(have_posts()): the_post(); 
                global $event;
                
                // Get the event ID
                $event_id = get_the_ID();

                // Get event banner settings
    $event_banner = get_post_meta($event_id, 'mec_banner', true);

    // TODO:  many of get_post_meta could be optimize, same for post thumbnail and many more

    // Determine which image to use
    if (!empty($event_banner['use_featured_image']) && $event_banner['use_featured_image'] == '1') {
        // Get featured image ID and details
        $thumbnail_id = get_post_thumbnail_id($event_id);
        $banner_image_src = wp_get_attachment_image_src($thumbnail_id, 'full');
        $banner_image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
    } elseif (!empty($event_banner['image'])) {
        // Use custom banner image provided in event settings
        $banner_image_src = array($event_banner['image']);
        // <!-- Debug banner_image_src: https://dev.komuna.warszawa.pl/wp-content/uploads/2024/09/Screenshot-2024-09-13-at-14.14.19.png -->
       // Get the image ID from the URL
    $banner_image_id = attachment_url_to_postid($event_banner['image']);
    
    // Retrieve the alt text using the image ID
    $banner_image_alt = get_post_meta($banner_image_id, '_wp_attachment_image_alt', true);
    

    } else {
        // Fallback if no image is provided
        $banner_image_src = array('');
        $banner_image_alt = '';
    }
            ?>
             <div class="custom-event-image-container-wrapper">
       
        <div class="custom-event-image-container">
            <?php if(!empty($banner_image_src[0])): ?>
                <?php
                $image_id = attachment_url_to_postid($banner_image_src[0]);
                $image_meta = wp_get_attachment_metadata($image_id);
                $image_srcset = wp_get_attachment_image_srcset($image_id, 'full');
                $image_sizes = wp_get_attachment_image_sizes($image_id, 'full');
                ?>
                <img src="<?php echo esc_url($banner_image_src[0]); ?>" 
                     alt="<?php echo esc_attr($banner_image_alt); ?>" 
                     class="custom-event-image"
                     width="<?php echo esc_attr($image_meta['width']); ?>"
                     height="<?php echo esc_attr($image_meta['height']); ?>"
                     srcset="<?php echo esc_attr($image_srcset); ?>"
                     sizes="<?php echo esc_attr($image_sizes); ?>"
                     decoding="async">
            <?php else: ?>
                <?php 
                the_post_thumbnail('full', array(
                    'class' => 'custom-event-image',
                    'srcset' => wp_get_attachment_image_srcset(get_post_thumbnail_id(), 'full'),
                    'sizes' => wp_get_attachment_image_sizes(get_post_thumbnail_id(), 'full'),
                )); 
                ?>
            <?php endif; ?>
        </div>
    </div>

            <div class=" custom-box-container info-container">
                
                <h1 class="custom-event-title"><?php the_title(); ?></h1>
                <!-- post excerpt -->
                <?php 
                $excerpt = get_the_excerpt();
                if (!empty($excerpt)) {
                    echo '<p class="custom-event-excerpt">' . $excerpt . '</p>';
                }
                ?>
            </div>
            <hr class="custom-event-divider">
            
          
            <div class="custom-box-container extra-info-container">
                <?php
                $start_date = get_post_meta(get_the_ID(), 'mec_start_date', true);
            
                
                $is_free = get_post_meta(get_the_ID(), 'mec_fields_17', true);

                echo '<div class="event-custom-fields">';
                    $duration = get_post_meta(get_the_ID(), 'mec_fields_9', true);
                    if (!empty($duration)) {
                        echo '<p class="event-duration">' . esc_html(pll__('Czas trwania')) . ': ' . esc_html($duration) . '</p>';
                    }
                    $ticketsInfo = get_post_meta(get_the_ID(), 'mec_fields_14', true);
                    if (!empty($ticketsInfo)) {
                        echo '<p class="event-tickets-info">' . esc_html(pll__('Bilety')) . ': ' . esc_html($ticketsInfo) . '</p>';
                    }
                    
                    $triggerWarning = get_post_meta(get_the_ID(), 'mec_fields_13', true);
                    if (!empty($triggerWarning)) {
                        echo '<p class="event-trigger-warning">' . esc_html(pll__('Ważne')) . ': ' . esc_html($triggerWarning) . '</p>';
                    }
                echo '</div>';
                ?>
            </div>
            <?php
            // Instantiate the MEC_feature_occurrences class
            $occurrences = new MEC_feature_occurrences();
            
            // Get the current timestamp
            $current_timestamp = current_time('timestamp');
            
            // Call the get_dates() function
            $dates = $occurrences->get_dates($event_id, $current_timestamp);

            // Display the list of occurrences
            if (!empty($dates)) {
                echo '<div class="custom-box-container event-occurrences-container">';
                // echo '<h2 class="event-occurrences-title">' . esc_html(pll__('Planowane wydarzenia')) . '</h2>';             
                echo '<ul class="event-occurrences-list">';
                foreach ($dates as $date) {
                    $start_timestamp = $date->tstart;
                    $end_timestamp = $date->tend;

                    // get mec_repeat_status from metadata
                    $mec_repeat_status = get_post_meta($event_id, 'mec_repeat_status', true);
                    // Get fields data from existing meta
                    $data_occurrences = (isset($event->data) && isset($event->data->meta) && isset($event->data->meta['mec_fields']) && is_array($event->data->meta['mec_fields'])) ? $event->data->meta['mec_fields'] : get_post_meta($event_id, 'mec_fields', true);
                
               

                    // Get metadata for this occurrence
                    $metadata = MEC_feature_occurrences::param($event_id, $start_timestamp, '*');
                    
                    // Determine if the event is sold out
                    $is_sold_out = false;
                    if ($mec_repeat_status == 0) {
                        $tickets_availability = get_post_meta($event_id, 'mec_fields_12', true);
                        $is_premiere = isset($data_occurrences[16]) ? $data_occurrences[16] : '';

                    } else if ($mec_repeat_status == 1) {
                        $tickets_availability = isset($metadata['fields'][12]) ? $metadata['fields'][12] : null;
                        $is_premiere = isset($metadata['fields'][16]) ? $metadata['fields'][16] : '';
                    } else {
                        $tickets_availability = null;
                        $is_premiere = '';
                    }
                    $is_sold_out = ($tickets_availability == 'wyprzedane') && $is_free !== 'tak';
                    $sold_out_class = $is_sold_out  ? ' sold-out' : '';
                    $strike_through_class = $is_sold_out ? ' sold-out--strike' : '';

                    echo '<li class="occurrence-event-item">';
                    echo '<div class="occurrence-event-details' . $sold_out_class . '">';
                    echo '<div class="occurrence-event-header">';
                    if ($is_premiere === 'tak') {
                        echo '<span class="premiere-label">' . esc_html(pll__('premiera')) . '</span>';
                    }
                    echo '<h3 class="occurrence-event-title' . $strike_through_class . '">' . esc_html(get_the_title()) . ' <span class="occurrence-event-date' . $strike_through_class . '">' . date('d.m', $start_timestamp) . ', ' . date('H:i', $start_timestamp) . '</span></h3>';
                    echo '</div>';
                    

                    // if the mec repeat status is 0 (from meta_data) then display the ticket link from post's metadata
                    if ($mec_repeat_status == 0) {
                        $ticket_link = get_post_meta($event_id, 'mec_fields_5', true);

                        $location_id = get_post_meta($event_id, 'mec_location_id', true);

                        $accessibility_features = get_post_meta($event_id, 'mec_fields_7', true);
                    } else if ($mec_repeat_status == 1) {
                        $ticket_link = isset($metadata['fields'][5]) ? $metadata['fields'][5] : null;

                        $accessibility_features = isset($metadata['fields'][7]) ? $metadata['fields'][7] : null;

                        // First check if there's a specific location for this occurrence
                        $location_id = isset($metadata['location_id']) ? $metadata['location_id'] : null;
                        
                        // If no specific location is set, use the main event's location
                        if (empty($location_id)) {
                            $location_id = get_post_meta($event_id, 'mec_location_id', true);
                        }
                    } else {
                        $ticket_link = null;

                        $accessibility_features = null;
                    }
 

                  
                    if ($location_id) {
                        $location = get_term($location_id, 'mec_location');
                        if (!is_wp_error($location) && !empty($location)) {
                            // Get location address from term meta instead of URL
                            $location_address = get_term_meta($location_id, 'address', true);
                            $location_url = !empty($location_address) ? 'https://maps.google.com/?q=' . urlencode($location_address) : '';
                            
                            echo '<p class="occurrence-event-location only-mobile">';
                            echo '<img src="' . esc_url(home_url('/wp-content/uploads/2024/10/location.svg')) . '" 
                                      alt="" 
                                      aria-hidden="true" 
                                      width="21" 
                                      height="20">';
                            echo '<span class="visually-hidden">' . esc_html(pll__('Lokalizacja')) . ': </span>';
                            
                            if (!empty($location_url)) {
                                echo '<a href="' . esc_url($location_url) . '" target="_blank" rel="noopener noreferrer">';
                                echo '<span>' . esc_html($location->name) . '</span>';
                                echo '</a>';
                            } else {
                                echo '<span>' . esc_html($location->name) . '</span>';
                            }
                            
                            echo '</p>';
                        }
                    }
                    if (isset($accessibility_features) && !empty($accessibility_features)) {
                        echo "<p class='accessibility-features only-mobile'>";
                        echo "<span class='visually-hidden'>" . esc_html(pll__('informacje o dostępności dla danego wydarzenia')) . ": </span>+ ";
                        if (is_array($accessibility_features)) {
                            echo esc_html(implode(', ', $accessibility_features));
                        } else {
                            echo esc_html($accessibility_features);
                        }
                        echo '</p>';
                    }
                    
 
                    if (isset($ticket_link) && !empty($ticket_link) && !$is_sold_out && $is_free !== 'tak') {
                        $event_date = date('d.m.Y', $start_timestamp);
                        echo '<a href="' . esc_url($ticket_link) . '" class="buy-ticket-button" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr(pll__('Kup bilet')) . ' ' . esc_attr(get_the_title()) . ' ' . esc_attr($event_date) . '">';
                        echo esc_html(pll__('Kup bilet'));
                        echo '<div class="svg-container">';
                        echo '<div class="svg-one">';
                        echo '<div class="svg-wrapper">';
                        include(get_stylesheet_directory() . '/assets/images/bilet.svg');
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<div class="svg-two">';
                        echo '<div class="svg-wrapper">';
                        include(get_stylesheet_directory() . '/assets/images/bilet.svg');
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</a>';
                    } else if ($is_sold_out && $is_free !== 'tak') {
                        echo '<span class="uppercase">' . esc_html(pll__('wyprzedane')) . '</span>';
                    }
                    echo '</div>';

                    if ($location_id) {
                        $location = get_term($location_id, 'mec_location');
                        if (!is_wp_error($location) && !empty($location)) {
                            // Get location address from term meta instead of URL
                            $location_address = get_term_meta($location_id, 'address', true);
                            $location_url = !empty($location_address) ? 'https://maps.google.com/?q=' . urlencode($location_address) : '';
                            
                            echo '<p class="occurrence-event-location tablet-and-higher">';
                            echo '<img src="' . esc_url(home_url('/wp-content/uploads/2024/10/location.svg')) . '" 
                                      alt="" 
                                      aria-hidden="true" 
                                      width="21" 
                                      height="20">';
                            echo '<span class="visually-hidden">' . esc_html(pll__('Lokalizacja')) . ': </span>';
                            
                            if (!empty($location_url)) {
                                echo '<a href="' . esc_url($location_url) . '" target="_blank" rel="noopener noreferrer">';
                                echo '<span>' . esc_html($location->name) . '</span>';
                                echo '</a>';
                            } else {
                                echo '<span>' . esc_html($location->name) . '</span>';
                            }
                            
                            echo '</p>';
                        }
                    }
                    // add sold out class to accessibility features if the event is sold out
                    if (isset($accessibility_features) && !empty($accessibility_features)) {
                        echo "<p class='accessibility-features tablet-and-higher'>";
                        echo "<span class='visually-hidden'>" . esc_html(pll__('informacje o dostępności dla danego wydarzenia')) . ": </span>+ ";
                        if (is_array($accessibility_features)) {
                            echo esc_html(implode(', ', $accessibility_features));
                        } else {
                            echo esc_html($accessibility_features);
                        }
                        echo '</p>';
                    }
                    

                    echo '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            ?>

            <div><?php the_content(); ?></div>
            <?php endwhile; // end of the loop. ?>
        </div>
    </section>
    </div>
    <?php do_action('mec_after_main_content'); ?>

    <style>
        
    </style>

<?php get_footer('mec');


