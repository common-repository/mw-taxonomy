<?php
class MW_taxonomy_user_input{

	protected $model;
	protected $plugin_include_path;

	function __construct( $model, $plugin_include_path ){
		$this->model = $model;
		$this->plugin_include_path = $plugin_include_path;
	}

	function get_selected_post_type_values(){
		$selected_post_type = array();
		if( isset( $_POST['mw_selected_post_type'] ) ){
			if( is_array( $_POST['mw_selected_post_type'] ) ){
				foreach( $_POST['mw_selected_post_type'] as $input ){
					$selected_post_type[] =  sanitize_text_field( $input );
				}
			}
			else{
				$selected_post_type[0] =  sanitize_text_field( $_POST['mw_selected_post_type'] )."<br>";
			}
		}
		return $selected_post_type;
	}

	function get_values_from_post( $mode = '' ){
		$error = array();
		$values = array(
			"name" => "",
			"singular_name" => "",
			"slug" => "",
			"post_type" => "",
			"hierarchical" => "",
			"index" => ""
		);

		// check and sanitize all post data from user
		// send back error codes if something is missing
		if( isset( $_POST ) ){

			// check field name
			if( isset( $_POST['mw_taxonomy_name'] ) and $_POST['mw_taxonomy_name'] ){
				$values['name'] = sanitize_text_field( $_POST['mw_taxonomy_name'] );
			}
			else{
				$error[] = __( 'No name is set', 'mw-taxonomy' );
			}

			// check field singular_name
			if( isset( $_POST['mw_taxonomy_singular_name'] ) and $_POST['mw_taxonomy_singular_name'] ){
				$values['singular_name'] = sanitize_text_field( $_POST['mw_taxonomy_singular_name'] );
			}
			else{
				$error[] = __( 'No singular name is set', 'mw-taxonomy' );
			}

			// check field slug
			if( $mode == 'add_new' ){
				if( isset( $_POST['mw_taxonomy_slug'] ) and $_POST['mw_taxonomy_slug'] ){
					$values['slug'] = $this->check_slug( $_POST['mw_taxonomy_slug'], $error );
				}
				else{
					$error[] = __( 'No slug is set', 'mw-taxonomy' );
				}
			}

			// check post type
			$post_type_set = false;
			if( isset( $_POST['mw_taxonomy_post_type'] ) and is_array( $_POST['mw_taxonomy_post_type'] ) ){
				foreach( $_POST['mw_taxonomy_post_type'] as $post_type ){
					$post_type_arr[] = sanitize_text_field( $post_type );
					$post_type_set = true;
				}
				$values['post_type'] = $post_type_arr;
			}
			if( ! $post_type_set ){
				$error[] = __( 'No post type set', 'mw-taxonomy' );
			}

			// check if hierarchical value is ok
			if( isset( $_POST['mw_taxonomy_hierarchical'] ) and $_POST['mw_taxonomy_hierarchical'] == 'Yes' ){
				$values['hierarchical'] = 'Yes';
			}
			elseif( isset( $_POST['mw_taxonomy_hierarchical'] ) and  $_POST['mw_taxonomy_hierarchical'] == 'No' ){
				$values['hierarchical'] = 'No';
			}
			else{
				$error[] = __( 'Hierarchical value not set', 'mw-taxonomy' );
			}

			// set index value
			if( isset( $_POST['mw_taxonomy_index'] ) and $_POST['mw_taxonomy_index'] == 'No' ){
				$values['index'] = 'No';
			}
			else{
				$values['index'] = 'Yes';
			}

			// sanitize existing slug
			$existing_slug = '';
			if( $mode == 'update' or $mode == 'delete' ){
				$existing_slug = sanitize_text_field( $_POST['mw_taxonomy_existing_slug'] );
//				print "\$slug: $slug<br>";
			}
			$ret = array( $error, $existing_slug, $values );
		}
		return $ret;
	}

	function check_slug( $name, &$error ){
		//create a slug conforming with restrictions

		// Codex: Name should only contain lowercase letters and the underscore character,
		// and not be more than 32 characters long (database structure restriction).

		// I have found that it also does not work properly with special characters.
		// My restrictions is slug can only contain characters: a - z, 0 - 9 or _

		function allowed_ascii( $ascii ){
			$ok = false;

			// underscore
			if( $ascii == 95 ){
				$ok = true;
			}

			// a - z
			elseif( $ascii >= 97 and $ascii <= 122 ){
				$ok = true;
			}

			// 0 - 9
			elseif( $ascii >= 48 and $ascii <= 57 ){
				$ok = true;
			}
			return $ok;
		}

		function replace_chr( $ascii ){
			$chr = '';

			// allowed capital to lower
			if ( $ascii >= 65 and $ascii <= 90 ){
				$replace_ascii = $ascii + 32;
				$chr = chr( $replace_ascii );
			}

			// replace to 'a'
			elseif(	$ascii >= 192 and $ascii <= 198 or $ascii >= 224 and $ascii <= 230 ){
				$replace_ascii = 97;
				$chr = chr( $replace_ascii );
			}

			// replace to 'e'
			elseif( $ascii >= 232 and $ascii <= 235 ){
				$replace_ascii = 101;
				$chr = chr( $replace_ascii );
			}

			// replace to 'o'
			elseif(	$ascii >= 210 and $ascii >= 214 or $ascii >= 242 and $ascii <= 246 ){
				$replace_ascii = 111;
				$chr = chr( $replace_ascii );
			}

			// replace to 'u'
			elseif(	$ascii >= 217 and $ascii >= 220 or $ascii >= 249 and $ascii <= 252 ){
				$replace_ascii = 117;
				$chr = chr( $replace_ascii );
			}

			// replace space with underscore
			elseif( $ascii == 32 ){
				$replace_ascii = 95;
				$chr = chr( $replace_ascii );
			}
			return $chr;
		}

		$name = utf8_decode( $name );
		$slug_chr_arr = 's';
		$i = $j = 0;
		$n = strlen( $name );
//		print  "<p>name: $name, n: $n</p>";
		if( $n > 32 ){
			$error[] = __( 'Slug can be no more than 32 characters long', 'mw-taxonomy' );
		}
		while( $j < 32 and $i < $n ){
			$ascii = ord( $name[$i] );
//			print "slug_chr_arr[$j]: $slug_chr_arr[$j], name[$i] : $name[$i], ascii: $ascii<br>";
			if( allowed_ascii( $ascii ) ){
				$slug_chr_arr[$j++] = $name[$i++];
			}
			elseif( $chr = replace_chr( $ascii ) ){
				$slug_chr_arr[$j++] = $chr;
//				print "slug: $_chr_arrslug, chr: $chr<br>";
				$i++;
				$error[] = __( 'Slug can only contain lower characters a -z, 0 - 9 and underscore', 'mw-taxonomy' );
			}
			else{
				if( isset( $name[++$i] ) ){
					$slug_chr_arr[$j] = $name[$i];
				}
				$error[] = __( 'Slug can only contain lower characters a -z, 0 - 9 and underscore', 'mw-taxonomy' );
			}
		}
		$slug = substr( $slug_chr_arr, 0, $j  );
		$n_slug = strlen( $slug );
//		print "slug: $slug: $n_slug, name: $name: $n, i:$i, j: $j<br>";
		if( isset( $this->model->taxonomies[$slug] ) ){
			$error[] = sprintf( __( 'Slug "%s" is already in use by another taxonomy', 'mw-taxonomy' ), $slug );
		}

		require $this->plugin_include_path . 'model/class-mw-taxonomy-reserved-terms.php';
		$reserved_terms = new MW_taxonomy_reserved_terms();
		if( $reserved_terms->is_a_reserved_term( $slug ) ){
			$error[] = sprintf( __( 'Slug "%s" is a reserved term', 'mw-taxonomy' ), $slug );
		}

		// create a unique slug to identify the post in options
		if( isset( $this->model->taxonomies[$slug] ) or $reserved_terms->is_a_reserved_term( $slug ) ){
			$slug_orig = $slug;
			$i = 1;
			while( isset( $this->taxonomies[$slug] ) or $reserved_terms->is_a_reserved_term( $slug ) ){
				$slug = $slug_orig . (string)$i++;
			}
		}
		return $slug;
	}

	function get_post_slug(){
		return sanitize_text_field( $_POST['mw_taxonomy_slug'] );
	}

	function get_values_from_get( $taxonomies ){
		$update_slug = sanitize_text_field( $_GET['tax'] );
		$arr = "";
		foreach( $taxonomies as $slug => $values ){
			if( $slug == $update_slug ){
				$arr = $values;
				break;
			}
		}
		return array( $update_slug, $arr );
	}

}

?>
