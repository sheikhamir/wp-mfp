<?php

namespace Elementor;
if ( ! defined( 'ABSPATH' ) ) exit; // If this file is called directly, abort.

class Multi_Filter_Portfolio extends \Elementor\Widget_Base {

    public function get_name() {
        return 'multi-filter-portfolio';
    }

    public function get_title() {
        return esc_html__( 'Multi Filter Portfolio', 'multi-filter-portfolio' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'multi-filter-portfolio' ];
    }

    public function get_keywords() {
        return [ 'hello', 'world' ];
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $filters['categories'] = get_terms( 'portfolio',
            array(
                'hide_empty' => 0,
                'include' => $settings['category_filter'] ? $settings['category_filter'] : '',
                'exclude' => $settings['category_exclude_filter'] ? $settings['category_exclude_filter'] : ''
            )
        );

        $filters['system_types'] = get_terms( 'project-system',
            array(
                'hide_empty' => 0,
                'include' => $settings['system_type_filter'] ? $settings['system_type_filter'] : '',
                'exclude' => $settings['system_type_exclude_filter'] ? $settings['system_type_exclude_filter'] : ''
            )
        );

        $filters['item_manufacturers'] = get_terms( 'project-manufacturer',
            array(
                'hide_empty' => 0,
                'include' => $settings['item_manufacturer_filter'] ? $settings['item_manufacturer_filter'] : '',
                'exclude' => $settings['item_manufacturer_exclude_filter'] ? $settings['item_manufacturer_exclude_filter'] : ''
            )
        );

        $filters['countries'] = get_terms( 'country',
            array(
                'hide_empty' => 0,
                'include' => $settings['countries_filter'] ? $settings['countries_filter'] : '',
                'exclude' => $settings['countries_exclude_filter'] ? $settings['countries_exclude_filter'] : ''
            )
        );
        /*
        $page = 1;
        $posts_per_page = 6; // Change this value to the number of posts you want to load each time

        $args = array(
            'post_type'      => 'portfolio',
            'posts_per_page' => $posts_per_page,
            'paged'          => $page,
            'orderby'        => 'rand', // Randomly order posts
        );

        $posts_query = new \WP_Query( $args );
        */
        ?>
        <div class="pulse-mfp-wrapper">
            <?php if ( ! empty( $filters ) && ! is_wp_error( $filters ) ): ?>
                <div class="row">
                    <?php foreach( $filters as $key => $filter ): ?>
                        <?php $filter_name = ucwords( str_replace( '_', ' ', $key ) ); ?>
                        <div class="col-sm-6 col-md-3">
                            <select class="ui fluid normal dropdown multiple pulse-mfp-filter <?php echo $key; ?>" data-filter-group="<?php echo strtolower( str_replace( '_', '-', $key ) ); ?>">
                                <option value="">All <?php echo ucwords( str_replace( '_', ' ', $key ) ); ?></option>
                                <?php foreach( $filter as $item ): ?>
                                    <?php $termslug = strtolower( $item->slug); ?>
                                    <?php $term_slug = str_replace( ' ', '-', $termslug ); ?>
                                    <option value=".<?php echo $term_slug; ?>"><?php echo $item->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div id="custom-post-container" class="pulse-mfp-container">
                <a href="#" class="pulse-mfp-post post"></a>
            </div>
            <button id="load-more-button" class="pulse-mfp-btn">More</button>
        </div>
        <?php
    }
}