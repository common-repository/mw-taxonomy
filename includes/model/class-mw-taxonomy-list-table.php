<?php
class MW_List_Table extends WP_List_Table {
	
	private $options;

	function __construct(){
		global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'taxonomy',     //singular name of the listed records
            'plural'    => 'taxonomies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

   /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'slug':
            case 'singular_name':
            case 'post_type':
            case 'hierarchical':
            case 'index':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_name($item){
        
		$page_main = 'mw-taxonomy';
		$page_new = 'mw-new-taxonomy';
		$delete_url = sprintf( "admin.php?page=%s&amp;mw_taxonomy_action=delete_try&amp;tax=%s", $page_main, $item['slug'] );
		$delete_nonce_url = wp_nonce_url( admin_url( $delete_url ), 'mw_taxonomy_form', 'mw_taxonomy_field' );

		$edit_url = sprintf( "admin.php?page=%s&amp;mw_taxonomy_action=edit&amp;tax=%s", $page_main, $item['slug'] );
		$edit_nonce_url = wp_nonce_url(  $edit_url , 'mw_taxonomy_form', 'mw_taxonomy_field' );

        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="%s">Edit</a>', $edit_nonce_url ),
            'delete'      => sprintf('<a href="%s">Delete</a>', $delete_nonce_url),
        );
        
        //Return the title contents
        return sprintf('<a href="%1$s"><strong>%2$s</strong></a> %3$s',
			$edit_nonce_url,
            $item['name'],
            $this->row_actions($actions)
        );

    }

	
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
//            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
 //           'ID'				=> __( 'ID', 'mw-taxonomy' ),
            'name'				=> __( 'Name Plural', 'mw-taxonomy' ),
            'singular_name'    	=> __( 'Name Singular', 'mw-taxonomy' ),
            'post_type'  		=> __( 'Post Type', 'mw-taxonomy' ),
            'hierarchical'    	=> __( 'Hierarchical', 'mw-taxonomy' ),
            'slug' 	    		=> __( 'Slug', 'mw-taxonomy' ),
            'index' 	    		=> __( 'Search Engine', 'mw-taxonomy' )
        );
        return $columns;
    }

	function set_options( $options ){
		$this->options = $options;
	}
	
	
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    public function prepare_items() {
  //      global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
 //       $this->process_bulk_action();
        
        

        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         
       $data = array();
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
       */
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/

		$data = array();
		
		if( is_array( $this->options ) ){
			foreach( $this->options as $slug => $values ){
//				print "$slug<br>";
				$text = "";
				$i = 1;
				if( is_array( $values ) ){
					foreach( $values['post_type'] as $post_type ){
						$text .= __( $post_type, 'mw-taxonomy' ) . "<br>";
					}
					$tax_arr = array(
//						'ID' 			=> $i++,
						'name' 			=> $values['name'],
						'singular_name' => $values['singular_name'],
						'post_type' 	=> $text,
						'hierarchical' 	=> __( $values['hierarchical'], 'mw-taxonomy' ),
						'slug' 			=> $slug,
						'index' 			=> __( $values['index'], 'mw-taxonomy' ),
					);
					$data[] = $tax_arr;
				}
			}
		}
                
  
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }

	
}

?>