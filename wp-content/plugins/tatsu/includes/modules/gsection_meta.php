<?php
// Change separator to divider in parser
if ( ! function_exists( 'tatsu_gsection_meta' ) ) {
	function tatsu_gsection_meta( $atts ) {

		$dynamic_atts = array();
		$temp_post_types = tatsu_get_custom_post_types();

		foreach( $temp_post_types as $post_type => $val){
			$dynamic_atts[$post_type] = '';
			$dynamic_atts[$post_type.'date'] = 'F j, Y';
		}
		$atts = shortcode_atts( array_merge( array(
			'alignment' => 'center',
			'title_font' => '',
			'post_type' => 'post',
			'meta_prefix' => '',
			'margin'	=> '0 0 15px 0',
			'key' => be_uniqid_base36( true ),
		), $dynamic_atts ),$atts );
		$custom_style_tag = be_generate_css_from_atts( $atts, 'tatsu_gsection_meta', $atts['key'], 'Global' );
		$custom_class_name = 'tatsu-'.$atts['key'];


		extract( $atts );
		$output = '';
		
		global $post;


		if ( !is_wp_error( get_the_terms( $post->ID,  $atts[$post_type]  ) ) ) {

			if( is_array( get_the_terms( $post->ID,  $atts[$post_type]  ) ) ) {
				$meta_val_array = array();
				foreach( get_the_terms( $post->ID,  $atts[$post_type] ) as $cat ) {
					array_push( $meta_val_array, $cat->name );
				}
				$meta_content = join( ", ", $meta_val_array );
			} else {
				$meta_content = get_the_terms( $post->ID,  $atts[$post_type] );
			}
			
		} else {
			switch( $atts[$post_type] ) {
				case 'date' :
					$meta_content = get_the_date( $atts[$post_type.'date'] ,$post->ID);
					break;
				case 'author' :
					$meta_content = get_the_author_meta( 'display_name', $post->post_author );
					break;
				default :
					if( is_array( get_post_meta( $post->ID,  $atts[ $post_type ], true ) ) ) {
						$meta_val_array = array();
						foreach( get_post_meta( $post->ID,  $atts[ $post_type ], true ) as $cat ) {
							array_push( $meta_val_array, $cat->name );
						}
						$meta_content = join( ", ", $meta_val_array );
					} else {
						$meta_content = get_post_meta( $post->ID,  $atts[ $post_type ] , true );
					}
			}
		}

		if( is_home() || is_archive() ){
			$meta_content = '';
		}


		$output .= '<div class="tatsu-module tatsu-gsection-meta ' . $custom_class_name . ' align-'.$alignment.'">';
		$output .= '<div class="'. $title_font .'" >'. $meta_prefix. $meta_content . '</div>';
		$output .= $custom_style_tag;
		$output .= '</div>';
		if( !empty( $meta_content ) ){
			return $output;
		} else {
			return '';
		}
		
	}
	add_shortcode( 'tatsu_gsection_meta', 'tatsu_gsection_meta' );
}

?>