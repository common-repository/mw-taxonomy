<?php

class MW_taxonomy{

	protected $version;
	protected $option_name;
	protected $options;
	protected $plugin_include_path;

	public function __construct(){
		$this->version = '1.0';
		$this->option_name = 'mw_taxonomy';
		$this->plugin_include_path = plugin_dir_path( __FILE__ );
	}

	public function run(){
		$this->actions();
		$this->load_files();
		if(is_admin()){
			require_once $this->plugin_include_path . 'controller/class-mw-taxonomy-admin.php';
			$admin = new MW_taxonomy_admin( $this->version, $this->option_name, $this->plugin_include_path );
		}
	}
	
	function load_files(){
		require $this->plugin_include_path . 'model/class-mw-taxonomy-widget.php';
		require $this->plugin_include_path . 'model/class-mw-taxonomy-register.php';
		
	}

	function actions(){
		add_action( 'init', array( $this, 'reg_taxonomies' ) );
		add_action( 'widgets_init', array( $this, 'register_my_widgets' ) );
		add_action( 'wp_head', array( $this, 'handle_indexing') );
	}
	
	function handle_indexing(){
		if( is_tax() ){
			$queried_object = get_queried_object();
			if( is_array( $this->options ) ){
				foreach( $this->options as $slug => $values ){
					if( $queried_object->taxonomy == $slug ){
						if( $values['index'] == 'No' ){
							print "\n<!-- MW Taxonomy -->\n";
							?><meta name="robots" content="noindex"/>
							
<?php
						}
						break;
					}
				}
			}
		}
	}
	
	function register_my_widgets(){
		register_widget( 'MW_taxonomy_widget' );
	}
	
	function reg_taxonomies(){
		$this->options = get_option( $this->option_name, false );
		if( is_array( $this->options ) ){
			foreach( $this->options as $slug => $values ){
				$tax_reg = new MW_taxonomy_register( $this->version, $slug, $values );
				$tax_reg->register();
			}
		}
	}
	


}

?>