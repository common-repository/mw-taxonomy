<?php
class MW_taxonomy_model{

	protected $option_name;
	protected $plugin_include_path;
	public $taxonomies;
	/*
		$taxonomies[$slug]	['name'] => $name
											['singular_name'] => $singular_name
											['description'] => $description
											['post_type'] => $post_type_arr
											['hierarchical'] => $hierarchical
											['indexl'] => $index
	*/


	public function __construct( $option_name, $plugin_include_path ){
		$this->option_name = $option_name;
		$this->read_taxonomies();
		$this->plugin_include_path = $plugin_include_path;
	}

	function count_taxonomies(){
		$n = 0;
		if( is_array( $this->taxonomies ) ){
			$n = count( $this->taxonomies );
		}
		return $n;
	}

	function read_taxonomies(){
		$this->taxonomies = array();
		$this->taxonomies = get_option( $this->option_name );
	}

	function set_selected_post_types( $post_types ){
		$post_type_option = $this->option_name . '_post_types';
		$changed = update_option( $post_type_option, $post_types );
		return $changed;
	}

	function get_selected_post_types(){
		$selected_post_types = get_option( $this->option_name . '_post_types' );
		return $selected_post_types;
	}

	function get_taxonomies(){
		return $this->taxonomies;
	}

	function test_values( $slug, $values ){
		$error_message = '';
		if( $slug == '' ){
			$return_message[] = 'No slug value';
		}
		if( ! is_array( $values ) ){
			$return_message[] = '\$values not array';
		}

	}

	function store_new_taxonomy( $values ){
		$success = false;

		// check integrity else return false
		if(  $values['name']  and $values['singular_name'] and $values['slug']  and $values['hierarchical'] and
				isset( $values['post_type'] ) and isset( $values['index'] ) and is_array( $values['post_type'] ) ){

			// store new values in options
			$slug = $values['slug'];
			$this->taxonomies[$slug] = $values;
			$success = update_option( $this->option_name, $this->taxonomies );
		}
		return $success;
	}

	function update_values( $update_slug, $update_values ){
		$success = false;
//		print "\$update_slug: $update_slug<br>";
		if( isset( $this->taxonomies[$update_slug] ) ){
//			print "singular_name: " . $this->taxonomies[$update_slug]['singular_name'] . "<br>";
			$this->taxonomies[$update_slug] = $update_values;
			$success = update_option( $this->option_name, $this->taxonomies );
		}
		return $success;
	}

	function delete_tax( $slug ){
		$delete_tax = '';
		$new_stored_tax = array();
		foreach( $this->taxonomies as $stored_slug => $stored_values ){
//				if( $stored_slug != $stored_values ){
			if( $stored_slug != $slug ){
				$new_stored_tax[$stored_slug] = $stored_values;
			}
			else{
				$delete_tax = $stored_values['name'];
			}
		}
		$success = update_option( $this->option_name, $new_stored_tax );
		return $delete_tax;
	}

} // class
?>
