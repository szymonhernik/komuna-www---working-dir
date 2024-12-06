<?php 

function rezydencje_archiwalne_shortcode() {
    // Define the query arguments
    $args = array(
        'post_type'      => 'page',           // Query pages
        'posts_per_page' => -1,               // Display all child pages
        'post_parent'    => 2700,             // Only get pages that are children of 'Archival Residencies' (ID 2700)
        'orderby'        => 'date',           // Order by date
        'order'          => 'DESC',           // Order descending (newest first)
    );

    // Run the query
    $query = new WP_Query($args);

    // Initialize an empty string to hold the output
    $output = '';
    $events_by_year = array();

    // Check if any posts were found
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            // Get the raw custom field value (from ACF)
            $start_date = get_field('poczatek_rezydencji', get_the_ID());
            $end_date = get_field('koniec_rezydencji', get_the_ID());

            // Extract year from the date string (assuming format is dd/mm/yyyy)
            $year = $start_date ? substr($start_date, -4) : substr($end_date, -4);

            // Store event data in the year group
            $events_by_year[$year][] = array(
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'excerpt' => get_the_excerpt(),
                'thumbnail' => get_the_post_thumbnail(get_the_ID(), 'medium', array('class' => 'img-fluid')),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'sort_date' => $start_date ? strtotime(str_replace('/', '-', $start_date)) : strtotime(str_replace('/', '-', $end_date)),
            );
        }

        // Sort years in descending order
        krsort($events_by_year);

        // Sort events within each year in descending order
        foreach ($events_by_year as &$year_events) {
            usort($year_events, function($a, $b) {
                return $b['sort_date'] - $a['sort_date'];
            });
        }

        $output .= '<div class="rezydencje-archiwalne">';
        foreach ($events_by_year as $year => $events) {
            $output .= "<h2 class='rezydencje-year-headline'>{$year}</h2>";
            $output .= '<hr>';
            $output .= '<div class="rezydencje-year-group">';

            foreach ($events as $event) {
                $output .= '<a href="' . $event['permalink'] . '" class="rezydencja-link">';
                $output .= '<div class="rezydencja-item">';
                
                $output .= '<div class="rezydencja-image">' . $event['thumbnail'] . '</div>';
                // wrap it in a div with class "rezydencja-item-content"
                $output .= '<div class="rezydencja-item-content">';
                $output .= '<h3>' . $event['title'] . '</h3>';
                
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</a>';
            }

            $output .= '</div>'; // Close rezydencje-year-group
        }
        $output .= '</div>'; // Close rezydencje-archiwalne
    } else {
        $output .= '<p>No archived residencies found.</p>';
    }

    // Reset post data after the custom query
    wp_reset_postdata();

    // Return the output
    return $output;
}

add_shortcode('custom_rezydencje_archiwalne', 'rezydencje_archiwalne_shortcode');
