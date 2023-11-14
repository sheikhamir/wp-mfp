<?php
/**
 * Plugin Name: Multi Filter Portfolio
 * Description: An elementor add-on that allows the user to filter portfolio items with multipl filters in a drop down.
 * Version:     1.0.0
 * Author:      Sheikh Amir
 * Text Domain: multi-filter-portfolio
 */


final class Multi_Filter_Portfolio {

    private static $_instance = null;
    private $mfp_widget;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        // Register the AJAX handler
        add_action( 'wp_ajax_mfp_load_more_posts', array( $this, 'mfp_load_more_posts' ) );
        add_action( 'wp_ajax_nopriv_mfp_load_more_posts', array( $this, 'mfp_load_more_posts' ) );
        add_action( 'wp_ajax_mfp_load_more_posts_json', array( $this, 'mfp_load_more_posts_json' ) );
        add_action( 'wp_ajax_nopriv_mfp_load_more_posts_json', array( $this, 'mfp_load_more_posts_json' ) );
        // Add Plugin actions
        add_action( 'elementor/widgets/register', [ $this, 'init_widgets' ] );
        // Register Widget Styles
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );
        // Register Widget Scripts
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'widget_scripts' ] );
    }

    public function init_widgets( $widgets_manager ) {
        require_once( __DIR__ . '/widgets/multi-filter-portfolio.php' );
        $this->mfp_widget = new \Elementor\Multi_Filter_Portfolio();
        $widgets_manager->register( $this->mfp_widget );
    }

    public function widget_styles() {
//        wp_enqueue_style( 'multi-filter-portfolio-semantic-transition-style', plugin_dir_url( __FILE__ ) . 'css/semantic-transition.css', array(), '2.4.2' );
//        wp_enqueue_style( 'multi-filter-portfolio-semantic-dropdown-style', plugin_dir_url( __FILE__ ) . 'css/semantic-dropdown.css', array(), '2.4.2' );
        wp_enqueue_style( 'multi-filter-portfolio-semantic-style', plugin_dir_url( __FILE__ ) . 'css/semantic.min.css', array(), '2.4.2' );
        wp_enqueue_style( 'multi-filter-portfolio-semantic-icon-style', plugin_dir_url( __FILE__ ) . 'css/semantic-icon.css', array(), '2.4.2' );
        // Enqueue Bootstrap from a CDN (you can replace the URL with the one you prefer)
        wp_enqueue_style( 'multi-filter-portfolio-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
        wp_enqueue_style( 'multi-filter-portfolio-style', plugin_dir_url( __FILE__ ) . 'css/multi-filter-portfolio.css', array('multi-filter-portfolio-bootstrap'), '1.0.0' );
    }

    public function widget_scripts() {
//        wp_enqueue_script( 'multi-filter-portfolio-semantic-transition-script', plugin_dir_url( __FILE__ ) . 'js/semantic-transition.js', array( 'jquery' ), '2.4.2', true );
//        wp_enqueue_script( 'multi-filter-portfolio-semantic-dropdown-script', plugin_dir_url( __FILE__ ) . 'js/semantic-dropdown.js', array( 'jquery' ), '2.4.2', true );
        wp_enqueue_script( 'multi-filter-portfolio-semantic-script', plugin_dir_url( __FILE__ ) . 'js/semantic.min.js', array( 'jquery' ), '2.4.2', true );
        
        wp_enqueue_script( 'multi-filter-portfolio-script', plugin_dir_url( __FILE__ ) . 'js/multi-filter-portfolio.js', array( 'jquery' ), '1.0.0', true );
        // Localize the ajax url variable for use in the JavaScript file
        wp_localize_script( 'multi-filter-portfolio-script', 'multi_filter_portfolio_ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mfp-nonce' ),
        ));
    }

    // AJAX handler to load more posts
    public function mfp_load_more_posts() {

        check_ajax_referer( 'mfp-nonce', 'security' );

        #$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        if ( is_home() || is_front_page()) {
            $page = (get_query_var('page')) ? get_query_var('page') : 1;
        } else {
            $page = (get_query_var('paged')) ? get_query_var('paged') : 1;
        }
        $posts_per_page =isset( $_POST['items'] ) ? absint( $_POST['items'] ) : 12; // Change this value to the number of posts you want to load each time

        $args = array(
            'post_type'      => 'portfolio',
            'posts_per_page' => $posts_per_page,
            'paged'          => $page,
            'orderby'        => 'rand', // Randomly order posts
        );

        $posts_query = new \WP_Query( $args );

        ob_start();
        if ( $posts_query->have_posts() ) {
            while ( $posts_query->have_posts() ) {
                $posts_query->the_post();
                // Checks if post has thumbnail
                $has_image = has_post_thumbnail();
                if ( $has_image ) {
                    $thumb_id = get_post_thumbnail_id();
                    $thumbnail = wp_get_attachment_url($thumb_id, 'full');
                }
                $terms = get_the_terms( get_the_ID(), 'portfolio' );

                if ( $terms && ! is_wp_error( $terms ) ) {
                    $links = array();
                    foreach ( $terms as $term ){
                        $links[] = $term->slug;
                    }
                    $links = str_replace(' ', '-', $links);
                    $tax   = join( " ", $links );
                    $taxi  = join( "  -  ", $links );
                } else {
                    $tax = '';
                }
                ?>
                <a href="#" class="post <?php echo mb_strtolower( $tax ); ?>">
                    <div class="overlay">
                        <h2><?php echo the_title(); ?></h2>
                    </div>
                    <?php if ($has_image): ?>
                        <div class="image-wrapper pulse-mfp-item-inner">
                            <div class="image" style="background-image:url('<?php echo $thumbnail; ?>')"></div>
                        </div>
                    <?php else: ?>
                        <div class="text-wrapper pulse-mfp-item-inner">
                            <div class="text"><?php echo the_title(); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php //the_excerpt(); ?>
                </a>
                <?php
            }
            wp_reset_postdata();
        }
        $posts_html = ob_get_clean();

        wp_send_json_success( $posts_html );
        wp_die();
    }

    // AJAX handler to load more posts
    public function _mfp_load_more_posts_json() {

        check_ajax_referer( 'mfp-nonce', 'security' );

        #$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        if ( is_home() || is_front_page()) {
            $page = (get_query_var('page')) ? get_query_var('page') : 1;
        } else {
            $page = (get_query_var('paged')) ? get_query_var('paged') : 1;
        }
        $posts_per_page =isset( $_POST['items'] ) ? absint( $_POST['items'] ) : 12; // Change this value to the number of posts you want to load each time

        $args = array(
            'post_type'      => 'portfolio',
            'posts_per_page' => $posts_per_page,
            'paged'          => $page,
            'orderby'        => 'rand', // Randomly order posts
        );

        $posts_query = new \WP_Query( $args );

        ob_start();
        if ( $posts_query->have_posts() ) {
            $item = [];
            $n = 0;
            while ( $posts_query->have_posts() ) {
                $posts_query->the_post();
                // Checks if post has thumbnail
                $has_image = has_post_thumbnail();
                if ( $has_image ) {
                    $thumb_id = get_post_thumbnail_id();
                    $item[$n]['thumbnail'] = wp_get_attachment_url($thumb_id, 'full');
                }
                $item[$n]['terms'] =  get_the_terms( get_the_ID(), 'portfolio' );
                if ( $item[$n]['terms'] && ! is_wp_error( $item[$n]['terms'] ) ) {
                    $links = array();
                    foreach ( $item[$n]['terms'] as $term ){
                        $links[] = $term->slug;
                    }
                    $links = str_replace(' ', '-', $links);
                    $item[$n]['tax']   = mb_strtolower(join( " ", $links ));
                    $taxi  = join( "  -  ", $links );
                } else {
                    $item[$n]['tax'] = '';
                }
                $item[$n]['title'] = the_title();
                $item[$n]['excerpt'] = the_excerpt();
                $n++;
            }
            wp_reset_postdata();
        }
        #$posts_html = ob_get_clean();

        //wp_send_json_success( $posts_html );
        header("Content-type: application/json");
        echo json_encode($item);
        wp_die();
    }

    public function mfp_load_more_posts_json() {

        check_ajax_referer( 'mfp-nonce', 'security' );

        if ( is_home() || is_front_page()) {
            $page = (get_query_var('page')) ? get_query_var('page') : 1;
        } else {
            $page = (get_query_var('paged')) ? get_query_var('paged') : 1;
        }
        $posts_per_page = isset( $_POST['items'] ) ? absint( $_POST['items'] ) : 12;

        $args = array(
            'post_type'      => 'portfolio',
            'posts_per_page' => /*$posts_per_page*/ -1,
            #'paged'          => $page,
            'orderby'        => 'rand',
        );

        $posts_query = new \WP_Query( $args );

        $posts_data = array(); // Initialize an array to hold the post data
        $total_posts = $posts_query->found_posts; // Get the total number of posts

        if ( $posts_query->have_posts() ) {
            while ( $posts_query->have_posts() ) {
                $posts_query->the_post();
                $post_data = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'thumbnail' => get_the_post_thumbnail_url(),
                    'taxonomies' => array(), // Initialize an array for taxonomies
                );

                // Get custom taxonomies
                $taxonomy_names = get_object_taxonomies( 'portfolio' );
                foreach ( $taxonomy_names as $taxonomy_name ) {
                    $terms = get_the_terms( get_the_ID(), $taxonomy_name );
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                        $term_names = wp_list_pluck( $terms, 'slug' );
                        $post_data['taxonomies'][ $taxonomy_name ] = $term_names;
                    }
                }

                // Get smaller thumbnail
                if ( has_post_thumbnail() ) {
                    $thumb_id = get_post_thumbnail_id();
                    // $thumbnail_small = wp_get_attachment_image_src( $thumb_id, 'small'/*[250, 250]*/ ); // Change 'thumbnail' to the desired image size
                    $thumbnail_small = wp_get_attachment_image_src( $thumb_id, 'thumbnail' ); 
                    // $thumbnail_small = wp_get_attachment_image_url($thumb_id, 'small');
                    if ( $thumbnail_small ) {
                        $post_data['thumbnail_small'] = $thumbnail_small[0];
                    }
                }

                $posts_data[] = $post_data;
            }
            wp_reset_postdata();
        }

        $response = array(
            'total_posts' => $total_posts,
            'posts' => $posts_data
        );

        wp_send_json_success( $response ); // Send the JSON response
        wp_die();
    }


}

Multi_Filter_Portfolio::instance();
