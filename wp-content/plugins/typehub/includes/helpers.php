<?php
/**
 * Helper functions used by the plugin.
 *
 * @link       http://brandexponents.com
 * @since      1.0.0
 *
 * @package    Typehub
 * @subpackage Typehub/public
 */

/*
// Function to minify dynamic css
// Ref : https://raw.githubusercontent.com/GaryJones/Simple-PHP-CSS-Minification/master/minify.php
*/
if( !function_exists( 'be_minify_css' ) ) {
	function be_minify_css( $css ) {

	// Normalize whitespace
	$css = preg_replace( '/\s+/', ' ', $css );

	// Remove spaces before and after comment
	$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );

	// Remove comment blocks, everything between /* and */, unless
	// preserved with /*! ... */ or /** ... */
	$css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );

	// Remove ; before }
	$css = preg_replace( '/;(?=\s*})/', '', $css );

	// Remove space after , : ; { } */ >
	$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );

	// Remove space before , ; { } ) > 
	$css = preg_replace( '/ (,|;|\{|}|\)|>)/', '$1', $css );

	// Strips leading 0 on decimal values (converts 0.5px into .5px)
	$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );

	// Strips units if value is 0 (converts 0px to 0)
	$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );

	// Converts all zeros value into short-hand
	$css = preg_replace( '/0 0 0 0/', '0', $css );

	// Shortern 6-character hex color codes to 3-character where possible
	$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );

	return trim( $css );

	}
}

if( !function_exists( 'be_split_number_text' ) ) {
	function be_split_number_text( $string ) {
		$length = strlen( $string );
		if( $length <= 0 ) {
			return array('', '');
		} 
		$i = $length-1;
		$text = '';
		$number = '';
		while( $i >= 0 ) {
			if( !is_numeric( $string[$i] ) ) {
				$text = $string[$i].$text;
			} else {
				$number = substr( $string, 0, $i+1 );
				break;
			}
			$i--;
		} 
		return array(
			$text,
			$number
		);
	}
}

if( !function_exists( 'be_split_unit_value' ) ) {
	function be_split_unit_value( $string ) {
		$value = be_split_number_text( $string );
		return array(
			'unit' => $value[0],
			'value' => $value[1]
		);
	}
}

if( !function_exists( 'be_extract_font_weight' ) ) {
	function be_extract_font_weight( $variant ) {
		$variant = (string) $variant;
		$weight = be_split_number_text( $variant );
		if( !empty( $weight[1] ) ) {
			return $weight[1];
		} else {
			return '400';
		}
	}
}

if( !function_exists( 'be_extract_font_style' ) ) {
	function be_extract_font_style( $variant ) {
		$style = be_split_number_text( $variant );
		if( !empty( $style[0] ) ) {
			return $style[0];
		} else {
			return 'normal';
		}
	}
}

if( !function_exists( 'be_standard_fonts' ) ) {
	function be_standard_fonts() {
		return array(
			"Arial"                     => "Arial, Helvetica, sans-serif",
			"Helvetica"                 => "Helvetica, sans-serif",    
			"Arial Black"               => "Arial Black, Gadget, sans-serif",
			"Bookman Old Style"         => "Bookman Old Style, serif",
			"Comic Sans MS"             => "Comic Sans MS, cursive",
			"Courier"                   => "Courier, monospace",
			"Garamond"                  => "Garamond, serif",
			"Georgia"                   => "Georgia, serif",
			"Impact"                    => "Impact, Charcoal, sans-serif",
			"Lucida Console"           => "Lucida Console, Monaco, monospace",
			"Lucida Sans Unicode"       => "Lucida Sans Unicode, Lucida Grande, sans-serif",
			"MS Sans Serif"             => "MS Sans Serif, Geneva, sans-serif",
			"MS Serif"                  => "MS Serif, New York, sans-serif",
			"Palatino Linotype"         => "Palatino Linotype, Book Antiqua, Palatino, serif",
			"Tahoma,Geneva"             => "Tahoma,Geneva, sans-serif",
			"Times New Roman"           => "Times New Roman, Times,serif",
			"Trebuchet MS"              => "Trebuchet MS, Helvetica, sans-serif",
			"Verdana"                   => "Verdana, Geneva, sans-serif",
			"System Font Stack"         => "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif",
		);
	}
}

if( !function_exists( 'be_get_font_family' ) ) {
	function be_get_font_family( $font ) {
		if( function_exists( 'typehub_get_store' ) ) {
			$store = typehub_get_store();
		}
		$standard_fonts = be_standard_fonts();
		$font = explode( ':', $font );
		if( !empty( $font[1] ) ){
			switch($font[0]){
				case 'schemes':
					$font_schemes = !empty( $store['fontSchemes'] ) ? $store['fontSchemes'] : array();
					if( !empty( $font_schemes[$font[1]] ) ){
						$family = be_get_font_family( $font_schemes[$font[1]]['fontFamily']);
					}else{
						$family = array(
							'source' => 'standard',
							'value' => $standard_fonts[ "System Font Stack" ],
						);
					}
					break;
				case 'typekit':
					if(!empty($store['settings']['typekitId'])){
						$typekit_data = get_typekit_data($store['settings']['typekitId']);
						if(!empty($typekit_data[$font[1]])){
							$family = array(
								'source' => 'typekit',
								'value' => $typekit_data[$font[1]]['cssname'],
							);
						}else{
							$family = array(
								'source' => 'standard',
								'value' => $standard_fonts[ "System Font Stack" ],
							);
						}
					}else{
						$family = array(
							'source' => false,
							'value' => $font[1],
						);	
					}
					break;
				case 'standard':
					if( array_key_exists( $font[1], $standard_fonts ) ) {
						$family = array(
							'source' => 'standard',
							'value' => $standard_fonts[$font[1]],
						);
					}
					break;
				default :
					$family = array(
						'source' => 'google-custom',
						'value' => $font[1],
					);
					break;
			}
		}else{
			$family = array(
				'source' => false,
				'value' => $font[0],
			);
		}
		return $family;  
	}
}

function typehub_google_fonts_url( $config ) {
    $font_url = '';
    
    /*
    Translators: If there are characters in your language that are not supported
    by chosen font(s), translate this to 'off'. Do not translate into your own language.
     */
    if ( 'off' !== _x( 'on', 'Google font: on or off', 'typehub' ) ) {
        $font_url = add_query_arg( 'family', $config, "//fonts.googleapis.com/css" );
    }
    return $font_url;
}
if( !function_exists( 'get_typekit_data' ) ) {
	function get_typekit_data($typekitId){
		$typekit_response = get_transient( 'typehub_typekit_'.$typekitId );
		if( !$typekit_response ) {
			$typekit_response = json_decode(file_get_contents('https://typekit.com/api/v1/json/kits/'.$typekitId.'/published'));
			set_transient( 'typehub_typekit_'.$typekitId, $typekit_response, 60*60*24*365 );
		}
		if( !empty( $typekit_response->kit ) ){
			$font_array = $typekit_response->kit->families;
			$typekit_fonts = array();
			$typekit_variant_helper = array(
				100 => "Ultra-Light",
				200 => "Light",
				300 => 'Book',
				400 => "Normal",
				500 => "Medium",
				600 => "Semi-Bold",
				700 => "Bold",
				800 => "Extra-Bold",
				900 => "Ultra-Bold",
			);
			if(isset($font_array)){
				foreach($font_array as $fam ){
					$temp_array = array();
					foreach($fam->variations as $vars){
						$temp_id = ((int) filter_var($vars, FILTER_SANITIZE_NUMBER_INT))*100;
						$temp_name = $typekit_variant_helper[$temp_id]." ". $temp_id;
						if( $vars[0] == 'i' ){
							$temp_id = $temp_id.'italic';
							$temp_name = $temp_name.' Italic';
						}
						array_push($temp_array,array(
							'id' => $temp_id,
							'name' => $temp_name,
						));
					}
					$typekit_fonts[$fam->name]["variants"] = $temp_array;
					$typekit_fonts[$fam->name]["subsets"] = [];
					$typekit_fonts[$fam->name]["cssname"] = $fam->css_names[0];
				}
			}
			return $typekit_fonts;
		} else {
			return false;
		}
		
	}
}

if( !function_exists( 'get_saved_fonts' ) ){
	function get_saved_fonts(){
		//$folders = list_files(get_home_path() . 'wp-content/plugins/typehub/includes/googlefonts' ,1,array('googlefonts'));
		$upload_dir = wp_upload_dir();
		$folders = glob($upload_dir['basedir'] . '/'.'typehub/*' , GLOB_ONLYDIR);
		$saved_fonts = array();
		foreach($folders as $folder => $value){
			$temp = explode( '/',$value);
			$temp = $temp[sizeof($temp) - 1];
			array_push($saved_fonts,$temp);
		}
		return $saved_fonts;
	}
}
if( !function_exists( 'get_google_fonts_by_user' ) ){
	function get_google_fonts_by_user(){
		$used_fonts = array();
		if( function_exists( 'typehub_get_store' ) ) {
			$store = typehub_get_store();
		}
		foreach ($store["savedValues"] as $option => $value) {
			if( array_key_exists( "font-family",$value ) ){
				$temp = explode( ':' ,$value['font-family']);
				if( $temp[0] === 'google' ){
					if( !in_array($temp[1],$used_fonts) ){
						array_push($used_fonts,$temp[1]);
					}
				}else if( $temp[0] === 'schemes' ){
				 	$fontName = !empty($store['fontSchemes'][ $temp[1] ][ 'fontFamily' ]) ? $store['fontSchemes'][ $temp[1] ][ 'fontFamily' ] : '' ;
					$fontName = explode(':',$fontName);
					 if( !empty($fontName[1]) && $fontName[0] === 'google' ){
						if( !in_array($fontName[1],$used_fonts) ){
							array_push($used_fonts,$fontName[1]);
						}
					 }
				}
			}
		}
		return $used_fonts;
	}
}



if( !function_exists('download_font_from_google') ){
	function download_font_from_google($font_name){
		$slugged_name = sluggify_font_name($font_name);
		$response = wp_remote_get( 'https://google-webfonts-helper.herokuapp.com/api/fonts/'.$slugged_name );
		if( !is_wp_error( $response ) ){
			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				require_once ( ABSPATH.'/wp-admin/includes/file.php' );
				WP_Filesystem();
			}
			$access_type = get_filesystem_method();
			$upload_dir = wp_upload_dir();
			$google_fonts_dir = $upload_dir['basedir'] . '/'. 'typehub/';

			if( 'direct' === $access_type ){
				$font_details =  json_decode( $response['body'] );
				$file_url = get_font_download_URL( $slugged_name,$font_details );

				$font_zip_file = download_url( $file_url, 30000 );

				if( !is_wp_error( $font_zip_file ) ){
					unzip_file( $font_zip_file, $google_fonts_dir.$font_name.'/' );
					write_css_link_to_file($font_name);
					return 'success';
				}else{
					return 'invalid zip';
				}
			}else{
				return 'permission denied';
			}
		}else{
			return 'error';
		}

	}
}

if( !function_exists( 'sluggify_font_name' ) ){
	function sluggify_font_name($name){
		$slugged_name = strtolower($name);
		$slugged_name = str_replace( " ","-",$slugged_name );
		return $slugged_name;
	}
}

if( !function_exists( 'get_weights_of_font' ) ){
	function get_weights_of_font( $font_family ){
		$weights = array();
		if( function_exists( 'typehub_get_store' ) ) {
			$store = typehub_get_store();
		}
		foreach ($store["savedValues"] as $option => $value) {
			if( array_key_exists( "font-family",$value ) ){
				$temp = explode(":",$value['font-family']);
				if( $temp[0] === 'schemes' ){
					$temp = explode(":",$temp);
				}
				if( $temp[0] === 'google' ){
					if($temp[1] === $font_family ){
						if( !in_array( $value['font-variant'],$weights ) ){
							array_push($weights,$value['font-variant']);
						}
					}
				}
			}
		}
		return $weights;
	}
}


if( !function_exists( 'write_css_link_to_file' ) ){
	function write_css_link_to_file($font_family){

		$slugged_name = sluggify_font_name($font_family);
		$response = wp_remote_get( 'https://google-webfonts-helper.herokuapp.com/api/fonts/'.$slugged_name );
		if( !is_wp_error( $response ) ){
		$font_details =  json_decode( $response['body'] );
		$upload_dir = wp_upload_dir();
		$google_fonts_dir = $upload_dir['basedir'] . '/'. 'typehub/';
		$font_name = $font_details->id; //font name slug
		$font_family = $font_details->family; //actual CSS font family name
		$version = $font_details->version;
		$variants = $font_details->variants;
		$subsets = join( '_', array_reverse($font_details->subsets) );
		$output = '';
		$weights = get_weights_of_font($font_family);
		foreach( $variants as $variant => $value ){
			
			$font_style = $value->fontStyle;
			$font_weight = $value->fontWeight;
			$variant = $value->id;
			$local = $value->local;

			$temp_variant = $variant;

			if( $temp_variant === 'regular' ){
				$temp_variant = '400';
			}else if( $temp_variant === 'italic' ){
				$temp_variant = '400italic';
			}

			if( in_array( $temp_variant, $weights ) ){
				$extensions = array( 'eot?#iefix','woff2','woff','ttf','svg#'.str_replace(" ",'',$font_family) );
				$formats = array( 'embedded-opentype','woff2','woff','truetype','svg' );
				$output .= "@font-face{\n";
				$output .= "font-family: '".$font_family."';\n";
				$output .= "font-style: ".$font_style.";\n";
				$output .= "font-weight: ".$font_weight.";\n";
				$output .= "src: url('./".$font_family."/".$font_name."-".$version."-".$subsets."-".$variant.".eot');\n";
				$output .= "src:local('".$local[0]."'), local('".$local[1]."'),\n";

				for($i = 0; $i < 5; $i++){
					$output .= "url('./".$font_family."/".$font_name."-".$version."-".$subsets."-".$variant.".".$extensions[$i]."') format('".$formats[$i]."'),\n";
				}
				$output = substr($output,0,-2).";\n}\n\n";
		}
	}
		$file = fopen($google_fonts_dir.'google-fonts.css',"a");
		fwrite($file,$output);
		fclose($file);
		}
	}
}

if( !function_exists( 'get_font_download_URL' ) ){
	function get_font_download_URL($slugged_name,$font_details){
			$subsets = $font_details->subsets;
			$variants = array();
			foreach( $font_details->variants as $variant => $value ){
				array_push($variants,$value->id);
			}
			$subsets = join( ',', array_reverse($subsets) );
			$variants = join( ',', $variants );
			return 'https://google-webfonts-helper.herokuapp.com/api/fonts/'.$slugged_name.'?download=zip&subsets='.$subsets.'&variants='.$variants;
	}
}

if( !function_exists( 'delete_unused_fonts' ) ){
    function delete_unused_fonts()
    {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ( ABSPATH.'/wp-admin/includes/file.php' );
			WP_Filesystem();
		}
		$used_fonts = get_google_fonts_by_user();
        $local_fonts = get_saved_fonts();

		$fonts_to_delete = array_diff( $local_fonts, $used_fonts );
		$upload_dir = wp_upload_dir();
		$google_fonts_dir = $upload_dir['basedir'] . '/'. 'typehub/';
		
		foreach( $fonts_to_delete as $font => $value ){
				
			delete_directory($google_fonts_dir.$value);
			
		}
		$cssFile = $google_fonts_dir.'google-fonts.css';
		if( file_exists($cssFile)  ){
			unlink( $cssFile );
		}
	}
}

if( !function_exists( 'delete_directory' ) ){
	function delete_directory($dirname) {
		if (is_dir($dirname))
		  $dir_handle = opendir($dirname);
	if (!$dir_handle)
		 return false;
	while($file = readdir($dir_handle)) {
		  if ($file != "." && $file != "..") {
			   if (!is_dir($dirname."/".$file))
					unlink($dirname."/".$file);
			   else
					delete_directory($dirname.'/'.$file);
		  }
	}
	closedir($dir_handle);
	rmdir($dirname);
	return true;
}
}

?>