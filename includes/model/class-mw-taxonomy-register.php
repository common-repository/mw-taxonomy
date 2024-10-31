<?php
class MW_taxonomy_register{

	protected $version;
	protected $values;
	protected $slug;

	public function __construct( $version, $slug, $values ){
		$this->version = $version;
		$this->slug = $slug;
		$this->values = $values;
	}
	
	public function get_option_arr(){
		return $this->values;
	}
	
	public function get_option_string(){
		$options_string = "$this->name,$this->singular_name,$this->hierarcical";
		if(is_array($this->post_type_arr)){
			foreach($this->post_type_arr as $post_type){
				$options_string .= ",$post_type";
			}
		}
		$options_string .= ";";
		return $options_string;
	}
	

	public function register(){
//		print "register(): " . $this->slug . "<br>";
		$object_type = $this->values['post_type'];
		
		$hierarchical = false;
		if( $this->values['hierarchical'] == 'Yes' ){
			$hierarchical = true;
		}
		
		$labels = array(
			"name" 	=> $this->values['name'],
			"singular_name" => $this->values['singular_name'],
			"all_items" => __( 'All', 'mw-taxonomy' ) . " " . $this->values['name'],
			"edit_item" => __( 'Edit', 'mw-taxonomy' ) . " " . $this->values['singular_name'],
			"view_item" => __( 'View', 'mw-taxonomy' ) . " " . $this->values['singular_name'],
			"update_item" => __( 'Update', 'mw-taxonomy' ) . " " . $this->values['singular_name'],
			"add_new_item" => __( 'Add New', 'mw-taxonomy' ) . " " . $this->values['singular_name'],
			"new_item_name" => sprintf( __( 'New %s Name', 'mw-taxonomy' ), $this->values['singular_name']),
			"search_items" => __( 'Search', 'mw-taxonomy' ) . " " . $this->values['name'],
			"popular_items" => __( 'Popular', 'mw-taxonomy' ) . " " . $this->values['name'],
			"items_list_navigation" => $this->values['name'] . " " .  __( 'list navigation', 'mw-taxonomy' ),
			"items_list" => $this->values['name'] . " " .  __( 'list', 'mw-taxonomy' ),
			"add_or_remove_items" => __( 'All', 'mw-taxonomy' ) . " " . $this->values['name']);
		if( $hierarchical ){
			$labels["parent_item"] = __( 'Parent', 'mw-taxonomy' ) . " " . $this->values['singular_name'];
			$labels["parent_item_colon"] = __( 'Parent', 'mw-taxonomy' ) . " " . $this->values['singular_name'] . ":";
		}
		else{
			$labels["seperate_items_with_commas"] = sprintf( __( 'Separate %s with commas', 'mw-taxonomy' ) , $this->values['name']);
			$labels["choose_from_most_used"] = __( 'Choose from the most used', 'mw-taxonomy' ) . " " . $this->values['name'];
			$labels["not_found"] = sprintf( __( 'No %s found', 'mw-taxonomy' ), $this->values['name'] );
		}
			
		$description_index = '';
		if( isset( $this->values['description'] ) ){
			$description_index = $this->values['description'];
		}
		$args = array(
			"labels" => $labels,
			"hierarchical" => $hierarchical,
			"public" => true,
			"show_in_nav_menus" => true,
			"show_in_rest" => true,
			"show_ui" => true,
			"show_tagcloud" => true,
			"description" => $description_index,
//			"rewrite" => array( 'slug' => $this->slug ),
			"show_admin_column" => true );
		register_taxonomy( $this->slug, $object_type, $args );
		
	}
}

?>
