<?php

/**
 * Plugin Name:     Stargps Shortcode
 * Plugin URI:      
 * Description:     Shortcodes tp display Tags , Categories , Related articles
 * Author:          Younes DRO
 * Author URI:      https://github.com/younes-dro
 * Text Domain:     stargps-shortcode
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Stargps_Shortcode
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class StarGPS_ShortCode {

    private static $instance;

    function __construct() {
        add_filter( 'use_widgets_block_editor', '__return_false' );
        add_shortcode('stargps_shortcode', array($this, 'run_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
    }

    public static function instance() {

        if (!self::$instance) {
            self::$instance = new StarGPS_ShortCode();
            self::$instance->setup_constants();
        }
        return self::$instance;
    }

    private function setup_constants() {
        if (!defined('STARGPSSHORTCODE_BASENAME'))
            define('STARGPSSHORTCODE_BASENAME', plugin_basename(__FILE__));
        if (!defined('STARGPSSHORTCODE_ROOTFILE'))
            define('STARGPSSHORTCODE_ROOTFILE', __FILE__);
        if (!defined('STARGPSSHORTCODE_PLUGIN_URL'))
            define('STARGPSSHORTCODE_PLUGIN_URL', plugin_dir_url(__FILE__));
        if (!defined('STARGPSSHORTCODE_PLUGIN_DIR'))
            define('STARGPSSHORTCODE_PLUGIN_DIR', plugin_dir_path(__FILE__));
    }

    public function run_shortcode() {
		/*
		global $wpdb;
		$wpdb->query('
    UPDATE '.$wpdb->prefix.'term_taxonomy AS tt    
    INNER JOIN
    '.$wpdb->prefix.'terms AS t
    ON t.term_id = tt.term_id
    SET tt.description = CONCAT ("Vente Installation ", t.name , " au Maroc")              
    WHERE tt.taxonomy = "category"');
	*/
			
        if (is_shop())
            return $this->shop();

        if (is_front_page() || is_home() || is_page('274'))
            return $this->front_page();

        if (is_page())
            return $this->page();

        if (is_product())
            return $this->product();

        if (is_single())
            return $this->single();
    }

    public function shop() {

        return;
    }

    public function front_page() {
        $html = '';
        $html .= '<div class="section"><div class="section_wrapper mcb-section-inner tagbox">';
        
        // New Products ( Nouveauté ) :
        $html .= '<h3>Nouveauté :</h3>';
        $new_products = array( 
            'post_type' => 'product', 
            'posts_per_page' => 1, 
            'product_cat' => 'nouveaute', 
            'orderby' => 'ID',
            'order' => 'DESC'   );
        $new_products_loop = new WP_Query( $new_products );
        $html .= '<ul>';
        while ( $new_products_loop->have_posts() ) : 
            $new_products_loop->the_post();
            $html .= '<li>';
            $html .= '<a class="taglink" href="'.get_permalink( $new_products_loop->post->ID ).'" title="'.esc_attr( $new_products_loop->post->post_title ).'">';
//            if (has_post_thumbnail( $new_products_loop->post->ID )) {
                $html .= get_the_post_thumbnail($new_products_loop->post->ID, 'shop_catalog');
//            } else {
//                $html .= '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" width="300px" height="300px" />';
//            }
            //$html .= '<h3>'.get_the_title() .'</h3>';
            $html .= '</a>';
            $html .= '</li>';
            
        endwhile;
        wp_reset_query();
        $html .= '</ul>';
        
        // Products tags :
        $terms = get_terms( 'product_tag' );
        
        $html .= '<h3>Étiquettes produits :</h3>';
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
            foreach ( $terms as $term ) {
                
                $html .= '<a  class="taglink"  href="'.get_term_link( $term->term_id, 'product_tag' ).'" title="Produits '.$term->name.'" >' . $term->name . '</a>';
               
                
            }
        }
		
        // Products category :
        $terms_cat = get_terms( 'product_cat' );
        
        $html .= '<h3>Catégories de produits :</h3>';
        if ( ! empty( $terms_cat ) && ! is_wp_error( $terms_cat ) ){
            foreach ( $terms_cat as $term_cat ) {
                
                $html .= '<a  class="taglink"  href="'.get_term_link( $term_cat->term_id, 'product_cat' ).'" title="'.$term_cat->name.' au Maroc">' . $term_cat->name . '</a>';
               
                
            }
        }

        // Last 5 Articles : 
        $the_query = new WP_Query('posts_per_page=8');
		$html .= '<h3>Derniers articles : </h3>';
        $html .= '<ul>';

        while ($the_query->have_posts()) : $the_query->the_post();

        $html .= '<li>';
		$html .= '<a title="' . get_the_title()  . '" href=' . get_permalink() . '>' ;
		$html .= get_the_post_thumbnail();
		$html .= get_the_title() ; 
		$html.= '</a>';
        $html .= '</li>';

        endwhile;
        wp_reset_postdata();
        $html .= '</ul>';
        // Categories
        $html .= '<h3>Catégories des articles :</h3>';
        foreach (get_categories() as $category){ 
			if ($category->count > 0){
				$html .= '<a  class="taglink"  href="'.get_category_link($category->term_id).'" title="'.$category->cat_name.'" >' . $category->cat_name . '</a>';
			}
		}
        // Tags
        $tags = get_tags();
        $html .= '<h3>Tags des articles: </h3>';
        foreach ($tags as $tag) {
            $html .= '<a  class="taglink" title="' . $tag->name . '" href="' . get_tag_link($tag->term_id) . '">' . $tag->name . '</a>';
        }
        $html .= '</div></div>';

        return $html;
    }

    public function page() {
        return ;
    }

    public function product() {
        return ;
    }

    public function single() {
        return ;
    }

    public function frontend_scripts() {

        wp_register_style('stargps-shortcode', STARGPSSHORTCODE_PLUGIN_URL . '/assets/css/stargps-shortcode.css', array(), time());
        wp_enqueue_style('stargps-shortcode');
                wp_register_script('stargps-shortcode-js', STARGPSSHORTCODE_PLUGIN_URL . '/assets/js/stargps-shortcode.js', array('jquery'), time());
        wp_enqueue_script('stargps-shortcode-js');
    }

}

function StarGPS_ShortCode_init() {
    $StarGPSShortCode = StarGPS_ShortCode::instance();
}

add_action('plugins_loaded', 'StarGPS_ShortCode_init');
/*
 * Remove Pages : panier / mon compte / from sitemap
 * 
 */
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function () {
    return array( 346, 348 , 347 );
} );
/*
 * Bing Meta
 */
function stargps_shortcode_theme_header_metadata() {
   if (  is_front_page() || is_home() ){
        
       echo '<meta name="msvalidate.01" content="F457A0332931B6E0D161299799C7376A" />';
   }

}
add_action( 'wp_head', 'stargps_shortcode_theme_header_metadata' );
function stargps_shortcode_theme_filter_wp_title( $title ){
	if(is_product() || is_product_tag() || is_product_category() ){
		return  $title . " au Maroc" ;
	}
	return $title;
	
}
add_filter( 'wpseo_title', 'stargps_shortcode_theme_filter_wp_title' );
