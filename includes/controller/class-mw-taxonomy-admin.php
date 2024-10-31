<?php

class MW_taxonomy_admin{

	protected $version;
	protected $option_name;
	protected $plugin_include_path;
	protected $view;
	protected $model;

	public function __construct( $version, $option_name, $plugin_include_path ){
		$this->version = $version;
		$this->option_name = $option_name;
		$this->plugin_include_path = $plugin_include_path;
		$this->actions();
		require $this->plugin_include_path . 'view/class-mw-taxonomy-view.php';
		$this->view = new MW_taxonomy_view( $this->option_name );
		require $this->plugin_include_path . 'model/class-mw-taxonomy-model.php';
		$this->model = new MW_taxonomy_model( $this->option_name, $plugin_include_path );
	}

	function actions(){
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_head', array( $this, 'style_column_slug') );
	}

	function style_column_slug(){
//		print "<!-- MW Taxonomy -->" . phpversion();
		print "
<style>
.column-slug {
	width: 10% !important;
}
</style>
";
	}

	function form_page(  $h1, $link, $button_value, $action, $mode,
				$update_slug, $user_input = array(), $message = ''){
		$this->view->wrap( $h1, $link );
		if( $message ){
			$this->view->message( $message );
		}
		if( !  $user_input )	$user_input['name'] = '';
		$args = array(
			'public'   => true,
			'_builtin' => true
		);
		$builtin_post_types = get_post_types( $args, 'objects' );

		$args = array(
			'public'   => true,
			'_builtin' => false
		);
		$custom_post_types = get_post_types( $args, 'objects' );

		$this->view->form( $action, $user_input, $builtin_post_types, $custom_post_types, $button_value, $mode, $update_slug, $message );
		$n_tax = $this->model->count_taxonomies();
		if( $this->model->count_taxonomies() ){
			$list_table = $this->get_List_Table();
			$list_table->set_options( $this->model->taxonomies );
			$list_table->prepare_items();
			$this->view->table( $list_table );
		}
		$this->view->end_div( 'wrap' );
	}

	function main_page(  ){
		$what_to_do = $this->do_something();
		$h1 = __( 'Custom Taxonomy Page', 'mw-taxonomy' );
		$message = '';
		$action = '';
		$update_slug = '';
		$mode = '';

		if( $what_to_do ){

			if( $what_to_do == 'new_form' ){
				$h1 = __( 'Create New Custom Taxonomy', 'mw-taxonomy' );
				$link = 'start';
				$button_value = __( 'Save', 'mw-taxonomy' );
				$action = 'add_new';
				$mode = 'new_form';
				$this->form_page( $h1, $link, $button_value, $action, $mode, $update_slug );
			}
			else{

				// data has been sent from user with a request
				require $this->plugin_include_path . 'controller/class-mw-taxonomy-user-input.php';
				$user_input = new MW_taxonomy_user_input( $this->model, $this->plugin_include_path );

				if( $what_to_do == 'add_new' ){
					list( $error, $update_slug, $sanitized_values ) = $user_input->get_values_from_post( 'add_new' );
					$is_error = count( $error );
					if( $is_error ){
						$message = '<span style="color:#dc143c;">' . __( 'Error', 'mw-taxonomy' ) . '</span>';
						$error = array_unique( $error );
						foreach( $error as $e ){
							$message .= '<br>' . $e;
						}
						$h1 = __( 'Create New Custom Taxonomy', 'mw-taxonomy' );
						$link = 'start';
						$button_value = __( 'Save', 'mw-taxonomy' );
						$action = 'add_new';
						$mode = 'new_form';
						$this->form_page( $h1, $link, $button_value, $action, $mode, $update_slug, $sanitized_values, $message );
					}
					else{
						$stored = $this->model->store_new_taxonomy( $sanitized_values );
						$this->model->read_taxonomies();
//						print "\$stored: $stored<br>";
						$message = $this->get_message($stored, $sanitized_values['singular_name'], 'saved');
						$h1 = __( 'Custom Taxonomy', 'mw-taxonomy' );
						$this->view->wrap( $h1, 'new' );
						$this->view->message( $message );
						$list_table = $this->get_List_Table();
						$list_table->set_options( $this->model->taxonomies );
						$list_table->prepare_items();
						$this->view->table( $list_table );
						$this->view->end_div( 'wrap' );
					}
				}

				// edit one taxonomy
				elseif( $what_to_do == 'edit' ){
					if( $_GET['tax'] ){
						list( $update_slug, $form_values ) = $user_input->get_values_from_get( $this->model->taxonomies );
						$h1 = __( 'Edit Taxonomy', 'mw-taxonomy' );
						$link = 'start';
						$button_value = __( 'Update Taxonomy', 'mw-taxonomy' );
						$action = 'update';
						$mode = 'edit';
						$this->form_page( $h1, $link, $button_value, $action, $mode, $update_slug, $form_values );
					}
					else{
						print 'Error! No taxonomy is set';
					}
				}
				// update existing taxonomy in options
				elseif( $what_to_do == 'update' ){
					$h1 = __( 'Edit Taxonomy', 'mw-taxonomy' );
					$link = 'start';
					$button_value = __( 'Update Taxonomy', 'mw-taxonomy' );
					$action = 'update';
					$mode = 'edit';
					list( $error, $update_slug, $update_values ) = $user_input->get_values_from_post( 'update' );
					$is_error = count( $error );
					if( $is_error ){
						$message = '<span style="color:#dc143c;">' . __( 'Error', 'mw-taxonomy' ) . '</span>';
						$error = array_unique( $error );
						foreach( $error as $e ){
							$message .= '<br>' . $e;
						}
					}
					else{
						$stored = $this->model->update_values( $update_slug, $update_values );
						$this->model->read_taxonomies();
						$message = $this->get_message( $stored, $update_values['singular_name'], 'updated' );
					}
					$this->form_page( $h1, $link, $button_value, $action, $mode, $update_slug, $update_values, $message );
				}
				// user tries to delete, show a warning page
				elseif( $what_to_do == 'delete_try' ){
					list( $update_slug, $form_values ) = $user_input->get_values_from_get( $this->model->taxonomies );
					$this->view->wrap( __( 'Delete Taxonomy', 'mw-taxonomy' ), 'start' );
					$this->view->message( __( 'You are about to delete this Taxonomy', 'mw-taxonomy' ) );
					$this->view->warning_page( $form_values, $update_slug );
					$this->view->end_div( 'wrap' );
				}
				// delete existing taxonomy, remove from options
				elseif( $what_to_do == 'really_delete' ){
					$slug = $user_input->get_post_slug();
					if( $deleted_tax = $this->model->delete_tax( $slug ) ){
						$message = sprintf( __( 'Taxonomy %s was deleted', 'mw-taxonomy' ), $deleted_tax );
					}
					$this->model->read_taxonomies();
					$this->home_page( $h1, $message );
				}
			}
//			list( $update_slug, $form_values ) = $this->get_values_from_post( $mode );

		}
		else{
			$this->home_page( $h1 );
		}
	}

	function home_page( $h1, $message = '' ){
		$this->view->wrap( $h1, true );
		if( $message ) $this->view->message( $message );
		if( is_array( $this->model->taxonomies ) ){
			$list_table = $this->get_List_Table();
			$list_table->set_options( $this->model->taxonomies );
			$list_table->prepare_items();
			$this->view->table( $list_table );
		}
		else{
			$this->view->no_tax();
		}
		$this->view->info();
		$this->view->end_div( 'wrap' );
	}

	function do_something(){
		$what_to_do = '';
			if( isset( $_REQUEST['mw_taxonomy_action'] ) ){
			if( check_admin_referer ( 'mw_taxonomy_form', 'mw_taxonomy_field' ) ){
				$what_to_do = $_REQUEST['mw_taxonomy_action'];
			}
		}
//		print "\$what_to_do: $what_to_do<br>";
		return $what_to_do;
	}

	function admin_menu(){

		// Main page
		$page_title = __( "Custom Taxonomy Management", 'mw-taxonomy' );
		$menu_title = __( "Taxonomy", 'mw-taxonomy' );
		$capability = "manage_options";
		$menu_slug = "mw-taxonomy";
		$function = array( $this, "main_page" );
		$icon_url = "";
		$position = "81";
		add_menu_page( $page_title, $menu_title, $capability,
			$menu_slug, $function, $icon_url, $position);
	}

	function get_List_Table(){
		if(!class_exists('WP_List_Table')){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		require $this->plugin_include_path . 'model/class-mw-taxonomy-list-table.php';
		return new MW_List_Table( 'mw-taxonomy' );
	}


	function get_message($stored, $single_name, $word){
		if( $stored ){
			$message = sprintf( __( 'Taxonomy %s %s', 'mw-taxonomy' ), $single_name, $word );
		}
		else{
			$message = sprintf( __( 'Taxonomy %s was not %s', 'mw-taxonomy' ), $single_name, $word );
		}
		return $message;
	}

}
?>
