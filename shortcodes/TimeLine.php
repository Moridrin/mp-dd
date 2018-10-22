<?php

namespace mp_dd\shortcodes;

use WP_Query;

abstract class TimeLine
{

    public static function timeLine($attributes, $innerHtml)
    {
        remove_filter('the_content', 'wpautop');
        if (!is_array($attributes)) {
            $attributes = [];
        }
        $attributes += [
            'paged'          => get_query_var('page'),
            'posts_per_page' => 10,
            'post_type'      => ['post', 'encounter'],
            'tax_query'      => [
                [
                    'taxonomy' => 'encounter_category',
                    'terms'    => [get_term_by('slug', 'container', 'encounter_category')->term_id],
                    'operator' => 'NOT IN',
                ],
            ],
        ];
        global $wp_query;
        $original_query = $wp_query;
        $wp_query       = new WP_Query($attributes);
        ob_start();
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                get_template_part('template-parts/content', get_post_format());
            }
            echo mp_ssv_get_pagination();
        } else {
            get_template_part('template-parts/content', 'none');
        }
        $wp_query = null;
        $wp_query = $original_query;
        wp_reset_postdata();
        return ob_get_clean();
    }
}

add_shortcode('timeline', [TimeLine::class, 'timeLine']);
