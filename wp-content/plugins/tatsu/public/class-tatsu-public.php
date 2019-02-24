<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.brandexponents.com
 * @since      1.0.0
 *
 * @package    Tatsu
 * @subpackage Tatsu/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tatsu
 * @subpackage Tatsu/public
 * @author     Brand Exponents Creatives Pvt Ltd <swami@brandexponents.com>
 */
class Tatsu_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	public function load_header_template($template) {
		if( current_theme_supports( 'tatsu-header-builder' ) ) {
			global $post;
			if ( TATSU_HEADER_CPT_NAME === $post->post_type && $template !== locate_template(array("single-tatsu_header.php"))){
				return TATSU_PLUGIN_DIR . "includes/header-builder/single-tatsu_header.php";
			}
		}
		return $template;
	}

	public function load_footer_template($template) {
		if( current_theme_supports( 'tatsu-footer-builder' ) ) {
			global $post;
			if ( TATSU_FOOTER_CPT_NAME === $post->post_type && $template !== locate_template(array("single-tatsu_footer.php"))){
				return TATSU_PLUGIN_DIR . "includes/footer-builder/single-tatsu_footer.php";
			}
		}
		return $template;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$assets_base_url = plugin_dir_url( __FILE__ );
		$cdn_address = apply_filters( 'tatsu_cdn_address', false );
		if( !empty( $cdn_address ) ) {
			$site_url = get_site_url();
			if( false !== strpos( $assets_base_url, $site_url ) ) {
				$assets_base_url = str_replace( $site_url, $cdn_address, $assets_base_url );
			}
		}
		if( !empty( $suffix ) ) {
			wp_enqueue_style( 'tatsu-main-css', $assets_base_url . 'css/tatsu.min.css', array(), $this->version, 'all' );
		}else{
			wp_enqueue_style( 'tatsu-vendor-css', $assets_base_url . 'css/vendor.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'tatsu-main-css', $assets_base_url . 'css/tatsu.css', array('tatsu-vendor-css'), $this->version, 'all' );
			wp_enqueue_style( 'tatsu-shortcodes', $assets_base_url . 'css/tatsu-shortcodes.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'tatsu-css-animations', $assets_base_url . 'css/tatsu-css-animations.css', array(), $this->version, 'all' );
			if( current_theme_supports('tatsu-header-builder') ) {
				wp_enqueue_style( 'tatsu-header-css', $assets_base_url . 'css/tatsu-header.css', array(), $this->version, 'all' );
			}
		}
		Tatsu_Icons::getInstance()->enqueue_styles();

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$assets_base_url = plugin_dir_url( __FILE__ );
		$vendor_scripts_url 	= $assets_base_url . 'js/vendor/';
		$cdn_address = apply_filters( 'tatsu_cdn_address', false );
		$version = defined( 'TATSU_VERSION' ) ? TATSU_VERSION : '1.0';
		if( !empty( $cdn_address ) ) {
			$site_url = get_site_url();
			if( false !== strpos( $assets_base_url, $site_url ) ) {
				$assets_base_url = str_replace( $site_url, $cdn_address, $assets_base_url );
				$vendor_scripts_url 	= $assets_base_url . 'js/vendor/';
			}
		}
		wp_enqueue_script( 'es6-promises-polyfill', $assets_base_url . 'js/vendor/es6-promise.auto' . $suffix . '.js', array(), false , true );
		wp_enqueue_script( 'asyncloader', $assets_base_url . 'js/vendor/asyncloader' . $suffix . '.js', array( 'jquery', 'es6-promises-polyfill' ), false , true );
		wp_enqueue_script( 'be-script-helpers', $assets_base_url . 'js/helpers' . $suffix . '.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'debouncedresize', $assets_base_url . 'js/vendor/debouncedresize' . $suffix . '.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name, $assets_base_url . 'js/tatsu' . $suffix . '.js', array( 'jquery','be-script-helpers', 'asyncloader','jquery-ui-core','jquery-ui-accordion','jquery-ui-tabs', 'debouncedresize' ), $this->version, true );

		$needed_scripts = array();
		foreach( glob( TATSU_PLUGIN_DIR.'public/js/vendor/*'. $suffix .'.js') as $dependency ) {
			if( '.min' === $suffix || false === strpos( $dependency, '.min.js' ) ) { 
				$current_index = basename( $dependency, $suffix . '.js' );
				$cur_dep = add_query_arg( 'ver',  $version, $vendor_scripts_url . basename( $dependency ) );
				$needed_scripts[ $current_index ] = esc_url( $cur_dep );
			}
		}
		wp_localize_script(
			$this->plugin_name, 
			'tatsuFrontendConfig', 
			array(
				'pluginUrl' => esc_url( TATSU_PLUGIN_URL ), 
				'vendorScriptsUrl' => esc_url( $vendor_scripts_url ),
				'mapsApiKey' => Tatsu_Config::getInstance()->get_google_maps_api_key(),
				'dependencies' => $needed_scripts,
				'slider_icons' 	=> tatsu_get_slider_icons(),
				'version'	=> $version
			) 
		);
		if( current_theme_supports('tatsu-header-builder') ) {
			wp_enqueue_script( 'tatsu-header-scripts', plugin_dir_url( __FILE__ ) . 'js/tatsu-header' . $suffix . '.js', array( 'jquery' ), $this->version, true );
		}

	}

	public function header_print() {
		if( current_theme_supports('tatsu-header-builder') ) {
			global $post;
			$post_type = get_post_type();
			$active_header_id = tatsu_get_active_header_id();
			if( TATSU_HEADER_CPT_NAME === $post_type ) {
				if( property_exists( $post, 'post_content' ) ) {
					echo apply_filters('the_content',$post->post_content);
				}
				return;
			}
			if( empty( $active_header_id ) ) {
				return;
			}
			$header = get_post( $active_header_id, 'ARRAY_A' );
			$header_content = $header[ 'post_content' ];
			$header_settings = get_post_meta( $active_header_id, 'tatsu_header_settings', true );
			$header_fonts = get_post_meta( $active_header_id, 'tatsu_header_fonts', true );
			$header_settings = !empty( $header_settings ) ? $header_settings : array();
			$header_fonts = !empty( $header_fonts ) ? $header_fonts : array();
		
			$header_global_style =  !empty( $header_settings['transparent'] ) ? 'transparent' : 'solid';
			$header_global_scheme = !empty( $header_settings['scheme'] ) ? $header_settings['scheme']  : '';
			$header_global_sticky = !empty( $header_settings['sticky'] ) ? 'sticky' : '';
			$header_global_smart = ( $header_global_sticky === 'sticky' && !empty( $header_settings['smart'] ) ) ? 'smart' : '';

			
			$header_options = array();
			if( !empty( $post ) ) {
				$post_id = tatsu_get_page_id();
				$header_options = get_post_meta( $post_id, '_tatsu_header_options' , true ); 		
			}
			// Page Header Settings
			
			$header_scheme = '';
			$header_style = '';
			$header_auto_pad = '';
			
			// Set Transparency Conditionally
			$allow_transparent = false; 
			$dynamic_func = [];
			$archive_list = !empty( $header_settings[ 'archive' ] ) ? $header_settings[ 'archive' ] : array();
			$single_list = !empty( $header_settings[ 'single' ] ) ? $header_settings[ 'single' ] : array();
			$taxonomy_list = !empty( $header_settings[ 'taxonomy' ] ) ? $header_settings[ 'taxonomy' ] : array();
			$other_list = !empty( $header_settings[ 'other' ] ) ? $header_settings[ 'other' ] : array();
			
			if (($key = array_search( 'post' , $archive_list)) !== false) {
				unset( $archive_list[$key] );
				array_push( $dynamic_func , 'home' );
			}

			if (($key = array_search( 'category' , $taxonomy_list)) !== false) {
				unset( $taxonomy_list[$key] );
				array_push( $dynamic_func , 'category' );
			}

			if (($key = array_search( 'tag' , $taxonomy_list)) !== false) {
				unset( $taxonomy_list[$key] );
				array_push( $dynamic_func , 'tag' );
			}

			$dynamic_func = array_merge( $dynamic_func, $other_list );

			if( empty($archive_list ) && empty($single_list) && empty($taxonomy_list) && empty($other_list) ){
				$allow_transparent = true;
			}else{
				if( is_singular( $single_list ) && sizeof( $single_list ) > 0 ){
					$allow_transparent = true;
				}else if ( is_tax( $taxonomy_list ) ){
					$allow_transparent = true;
				}else if( is_post_type_archive( $archive_list ) && sizeof( $archive_list ) > 0 ){
					$allow_transparent = true;
				}else{
					for($i = 0; $i < sizeof( $dynamic_func ); $i++ ){
						$function_to_execute = 'is_'.$dynamic_func[ $i ];
						if( call_user_func( $function_to_execute ) ){
							$allow_transparent = true;
							break;
						}
					}
				}
			}

			if( $allow_transparent ){
				$header_style = ( $header_options && array_key_exists( 'tatsu_page_header_style' , $header_options ) && $header_options['tatsu_page_header_style'] !== 'inherit' ) ? $header_options['tatsu_page_header_style'] : $header_global_style;
				$header_scheme = ( $header_options && array_key_exists( 'tatsu_page_header_scheme' , $header_options ) && $header_options['tatsu_page_header_scheme'] !== 'inherit' ) ? $header_options['tatsu_page_header_scheme'] : $header_global_scheme;
			}

			if( 'transparent' === $header_style ) {
				$header_auto_pad = ( empty( $header_options ) || !array_key_exists( 'tatsu_header_auto_pad', $header_options ) || 'no' !== $header_options[ 'tatsu_header_auto_pad' ] ) ? 'header-auto-pad' : '';
			}

			echo '<div id="tatsu-header-container">';
			echo '<div id="tatsu-header-wrap" class="'.$header_global_smart.' '.$header_global_sticky.' '.$header_style.' '.$header_scheme.' ' . $header_auto_pad . '">';

			echo do_shortcode( $header_content );
			echo '</div>';
			echo '<div id="tatsu-header-placeholder"></div>';
			echo '</div>';
		}
	}

	public function print_footer() {
		if( current_theme_supports('tatsu-footer-builder') ) {
			global $post;
			$post_type = get_post_type();
			$active_footer_id = tatsu_get_active_footer_id();
			if( TATSU_FOOTER_CPT_NAME === $post_type ) {
				if( property_exists( $post, 'post_content' ) ) {
					echo apply_filters('the_content',$post->post_content);
				}
				return;
			}
			if( empty( $active_footer_id ) ) {
				return;
			}
			$footer = get_post( $active_footer_id, 'ARRAY_A' );
			$footer_content = $footer[ 'post_content' ];
			do_action( 'tatsu_before_footer_builder_content' );
			?>
				<div id = "tatsu-footer-container">
					<?php echo do_shortcode( $footer_content ); ?>
				</div>
			<?php
			do_action( 'tatsu_after_footer_builder_content' );
		}
	}

	public function header_css_print() {
		if( current_theme_supports('tatsu-header-builder') ) {
			$active_header_id = tatsu_get_active_header_id();
			if( !empty( $active_header_id ) ) {
				$header_store = new Tatsu_Header_Store( $active_header_id );
				$header_content = $header_store->get_header_store();
				// echo tatsu_header_css_print( $header_content['tatsu_page_content']['inner'] ); 
			}
		}
	}

	/**
	 * Single Page Site
	*/
	public function single_page_site( $classes ) {
		global $post;
		if( !empty( $post ) ) {
			$header_meta_options = get_post_meta( $post->ID, '_tatsu_header_options' , true ); 	
			$single_page_site = ( $header_meta_options && array_key_exists( 'tatsu_header_single_page_site' , $header_meta_options ) ) ? $header_meta_options['tatsu_header_single_page_site'] : 0;
			if( $single_page_site ) {
				$classes[] = 'tatsu-header-single-page-site';
			}
		}
		return $classes;
	}


	public function sliding_menu() {
		if( current_theme_supports('tatsu-header-builder') ) {
			$active_header_id = tatsu_get_active_header_id();
			if( !empty( $active_header_id ) ) {
				$header_store = new Tatsu_Header_Store( $active_header_id );
				$header_content = $header_store->get_header_store();
				$header_content	= $header_content['tatsu_page_content']['inner'];
				$output = '';
				foreach( $header_content as $rows ) {
					//$columns = $rows['inner'];
					if( empty( $rows['inner'] ) || !is_array( $rows['inner'] ) ) {
						continue;
					}
					foreach( $rows['inner'] as $column ) {
						//$modules = $column['inner'];
						if( empty( $column['inner'] ) || !is_array( $column['inner'] ) ) {
							continue;
						}
						foreach( $column['inner'] as $module ) {
							//$slide_menu_cols = $module['inner'];
							if( empty( $module['inner'] ) || !is_array( $module['inner'] ) ) {
								continue;
							}
							if( 'tatsu_hamburger_menu' === $module['name'] ) {
								$output .= '<div id="tatsu-'.$module['id'].'" class="tatsu-'.$module['id'].' tatsu-slide-menu">
												<div class="tatsu-slide-menu-inner">';
													$output .= do_shortcode( tatsu_shortcodes_from_content( $module['inner'] ) );
										
								$output .= 		'</div>'; // Menu Inner
								$output .= '</div>'; // Menu 
							}
						}
					}
				}
				echo $output;
				echo '<div id="tatsu-fixed-overlay"></div>';
			}
		}
	}

	public function tatsu_add_global_section_classes( $section_position ) {
		add_filter('tatsu_section_classes', "tatsu_global_section_add_{$section_position}_class");
	}

	public function tatsu_remove_global_section_classes( $section_position ) {
		remove_filter('tatsu_section_classes', "tatsu_global_section_add_{$section_position}_class");
	}

	public function tatsu_add_global_sections(){
		$post_id = get_the_ID();
		$post_type = get_post_type( $post_id );
		if( current_theme_supports( 'tatsu-global-sections' ) && 'tatsu_gsections' !== $post_type ) {
			$section_positions = array();
			if( empty( $post_type ) ){
				$post_type = '';
			} else {
				global $wp_query;
				if( is_home() ){
					$post_type = 'archive-'.$post_type;
				} else if( is_archive() ){	
					if( array_key_exists( 'post_type', $wp_query->query ) ){
						$post_type = $wp_query->query['post_type'];
					}
					$post_type = 'archive-'.$post_type;
				}else {
					if( array_key_exists( 'post_type', $wp_query->query ) ){
						$post_type = $wp_query->query['post_type'];
					}
					$post_type = 'single-'.$post_type;
				}
			}
			$is_others_pages = tatsu_is_others_page_type();
			if( $is_others_pages[0] ){
				$post_type = $is_others_pages[1];
				$post_id = null;
			}

			if( current_action() === 'tatsu_head' ){
				$section_positions = array( 'top' );
			}else if( current_action() === 'tatsu_footer' ){
				$section_positions = array( 'penultimate','bottom' );
			}

			foreach( $section_positions as $section_position ){
				do_action( "tatsu_global_section_before_output", $section_position );
				$content_to_be_added = 0;
				$global_section_data = get_option( 'tatsu_global_section_data', array() );
				if( gettype( $global_section_data ) === 'string' ){
					$global_section_data = json_decode( $global_section_data,true );
				}
				if( array_key_exists('post_settings',$global_section_data) ){
					$post_settings = $global_section_data['post_settings'];
				}else{
					$post_settings = array();
				}

				$temp_post_type = $post_type;
				//$temp_post_id = $post_id;				

				if( !( array_key_exists( $post_type, $post_settings ) &&
					array_key_exists( $section_position, $post_settings[ $post_type ] ) ) ){

					if( array_key_exists( 'all',$post_settings ) ){

						if( array_key_exists( $section_position, $post_settings['all']) ){
							$temp_post_type = 'all';
							//$temp_post_id = null;
						}
						
					}

				}

				if( array_key_exists( $temp_post_type,$post_settings ) ){
					$value_for_post_type = $post_settings[ $temp_post_type ];
					if( array_key_exists( $section_position, $value_for_post_type ) && $value_for_post_type[ $section_position ] !== 'none' ){
						$content_to_be_added = (int) $value_for_post_type[ $section_position ];
					}
					$post_meta = get_post_meta( $post_id,'_tatsu_global_section_on_post' );
					if( !empty( $post_meta ) && $post_meta[0][$section_position] !== 'inherit' ){
						if( $post_meta[0][$section_position] === 'none'  ){
							$content_to_be_added = 0;
						}else{
							$content_to_be_added = (int) $post_meta[0][ $section_position ];
						}
					}
					if( $content_to_be_added ){
						echo do_shortcode( get_post( (int) $content_to_be_added )->post_content);
					}
				}
				do_action( "tatsu_global_section_after_output", $section_position );
			}
		}
	}
}