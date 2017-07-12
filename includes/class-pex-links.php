<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die();
}
/**
 * The Pex Links Core Plugin Class.
 */
class Pex_Links {

    public static $plugin_name   = 'Experiences';
    public static $singular_name = 'Pex Link';
    public static $plugin_slug   = 'pex-links';
    public static $text_domain   = 'pex-links';
    public static $post_type     = 'pex-links';
    public static $taxonomy      = 'pex-links-cat';
    public static $slug          = 'go';
    public static $settings      = false;

    public function __construct() {

        add_action( 'init', array( $this, 'register_pex_links' ) );
        add_action( 'init', array( $this, 'register_pex_links_cat') );
        add_action( 'after_wp_tiny_mce', array( $this, 'get_button_settings_template' ) );
        add_action( 'template_redirect', array( $this, 'redirect' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        if( is_admin() ){
            require_once PEX_LINKS_PLUGIN_DIR . 'admin/class-pex-links-metabox.php';
            require_once PEX_LINKS_PLUGIN_DIR . 'admin/class-pex-links-buttons.php';
        }

        require_once PEX_LINKS_PLUGIN_DIR . 'includes/class-pex-links-shortcode.php';

        self::$settings = get_option( 'pex_links_settings' );

        if( isset( self::$settings['category'] ) ) {

            if( self::$settings['category'] == '1' ) {

                add_action( 'init', array( $this, 'register_rewrite_rules' ) );
                add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 3 );

            }

        }

    }

    /**
     * Load translations
     */
    function load_textdomain() {

        load_plugin_textdomain( self::$text_domain, false, dirname( PEX_LINKS_BASENAME ) . '/languages/' );

    }

    /**
    * Get Slug for Link
    */
    public function get_slug() {

        if( !empty( self::$settings['slug'] ) ) {

           return self::$settings['slug'];

        } else {

           return self::$slug;

        }

        return 'go';

    }

    /**
     * Register Custom Post Type.
     */
    public function register_pex_links() {

        $labels = array(
            'name'           => _x( self::$plugin_name, 'Post Type General Name', 'pex-links' ),
            'singular_name'  => _x( self::$singular_name, 'Post Type Singular Name', 'pex-links' ),
            'menu_name'      => __( self::$plugin_name, 'pex-links' ),
            'name_admin_bar' => __( self::$plugin_name, 'pex-links' ),            
            'add_new_item'   => __( 'Add New Experience', 'pex-links' ),
            'add_new'        => __( 'Add New', 'pex-links' ),
            'new_item'       => __( 'New Link', 'pex-links' ),
            'edit_item'      => __( 'Edit Link', 'pex-links' ),
            'update_item'    => __( 'Update Link', 'pex-links' ),
            'view_item'      => __( 'View Link', 'pex-links' ),
            'search_items'   => __( 'Search Experience', 'pex-links' )
        );

        $slug = $this->get_slug();

        $rewrite = array(
            'slug'       => $slug,
            'with_front' => false,
            'pages'      => false,
            'feeds'      => false,
        );

        $args = array(
            'label'               => __( self::$singular_name, 'pex-links' ),
            'description'         => __( 'Pex Link Post Type', 'pex-links' ),
            'labels'              => $labels,
            'supports'            => array( 'title' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-admin-links',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'rewrite'             => $rewrite,
            'capability_type'     => 'page',
        );

        $args = apply_filters( 'px_link_register_post_type', $args );

        register_post_type( self::$post_type, $args );

    }

    /**
     * Register Custom Post Type.
     */
    public function register_pex_links_cat() {

        $labels = array(
            'name'          => _x( 'Categories', 'Taxonomy General Name', 'pex-links' ),
            'singular_name' => _x( 'Category', 'Taxonomy Singular Name', 'pex-links' ),
            'menu_name'     => __( 'Categories', 'pex-links' )
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
            'rewrite'           => false,
        );

        $args = apply_filters( 'px_link_register_taxonomy', $args );

        register_taxonomy( self::$taxonomy, array( self::$post_type ), $args );

    }

    public function register_rewrite_rules() {

        $slug = $this->get_slug();

        add_rewrite_rule( "$slug/([^/]+)?/?$", 'index.php?' . self::$post_type . '=$matches[1]', 'top' );
        add_rewrite_rule( "$slug/([^/]+)?/?([^/]+)?/?", 'index.php?' . self::$post_type . '=$matches[2]&' . self::$taxonomy . '=$matches[1]', 'top' );

    }

    /**
     * Modify Post Type Link
     */
     public function post_type_link( $permalink, $post_id, $leavename ) {

        $post = get_post( $post_id );

        if ( !empty( $permalink ) ) {

            if ( $post->post_type == self::$post_type ) {

                $terms = wp_get_object_terms( $post->ID, self::$taxonomy );

                if( !empty( $terms ) ) {

                    usort( $terms, '_usort_terms_by_ID' ); // order by ID

                    $term = $terms[0]->slug;

                    if( !empty( $term ) ) {

                        $slug = $this->get_slug();

                        $permalink = str_replace( $slug, $slug . '/' . $term , $permalink );

                    }

                }

            }

        }

        return $permalink;

    }

    /**
     * Activation Hook
     */
    public function activation_hook() {

        // Register post type and taxonomy
        $this->register_pex_links();
        $this->register_pex_links_cat();

        // Flush the permalinks rules
        flush_rewrite_rules();

        if ( get_option( 'pex_links_settings' ) === false ) {

            $preset_options = array(
                'slug'     => $this->get_slug(),
                'default'  => get_home_url(),
                'nofollow' => 1,
                'stats'    => 1,
                'redirect' => 301,
            );

            add_option( 'pex_links_settings', $preset_options );

        }

    }

    /**
     * Deactivation Hook
     */
    public function deactivation_hook() {

        // Flush the permalinks rules
        flush_rewrite_rules();

    }

    /**
     * Redirect pex link.
     */
    public function redirect() {

        if ( is_singular( Pex_Links::$post_type ) ) {

            $post_id       = get_the_ID();
            $target_url    = get_post_meta( $post_id, '_pex_links_target', true );
            $redirect_type = get_post_meta( $post_id, '_pex_links_redirect', true );
            $nofollow      = get_post_meta( $post_id, '_pex_links_nofollow', true );

            if( empty( $target_url ) ){

                if( !empty( self::$settings['default'] ) ) {
                    $target_url = self::$settings['default'];
                } else {
                    $target_url = home_url( '/' );
                }

            }

            $this->count_stats( $post_id );

            if( empty( $redirect_type ) ) {

                if( empty( self::$settings['redirect'] ) ) {
                    $redirect_type = 301;
                } else {
                    $redirect_type = (int) self::$settings['redirect'];
                }

            }

            if( $nofollow ) {
                $nofollow_header = 'X-Robots-Tag: noindex, nofollow';
                $nofollow_header = apply_filters( 'px_link_nofollow_header', $nofollow_header );
                header( $nofollow_header, true );
            }

            $target_url    = apply_filters( 'px_link_target_url', $target_url );
            $redirect_type = apply_filters( 'px_link_redirect_type', $redirect_type );

            do_action( 'px_link_before_redirect', $post_id, $target_url, $redirect_type );

            wp_redirect( esc_url_raw( $target_url ), $redirect_type );
            exit();

        }

    }

    /**
     * Count link hits and update post_meta.
     */
    public function count_stats( $post_id ) {

        if( !empty( self::$settings['stats'] ) ) {

            $current = (int) get_post_meta( $post_id, '_pex_links_stat', true );
            update_post_meta( $post_id, '_pex_links_stat', $current + 1 );

        }

    }

    public function get_button_settings_template() {
        $template = PEX_LINKS_PLUGIN_DIR . '/admin/partials/wysiwyg-button-dialog.php';

        if ( file_exists( $template ) ) {
            require_once PEX_LINKS_PLUGIN_DIR . '/admin/partials/wysiwyg-button-dialog.php';
        }
    }

    public function get_links() {
        $args  = array(
            'post_type'      => 'pex-links',
            'post_status'    => 'publish',
            'posts_per_page' => - 1
        );
        $links = new WP_Query( $args );

        return $links->get_posts();
    }

}