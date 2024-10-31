<?php
//print 'class MW_taxonomy_widget extends WP_Widget<br>';

class MW_taxonomy_widget extends WP_Widget{

	function __construct(){
		$widget_opts = array(
			'description' => esc_html__( "A list or dropdown of a selected custom taxonomy.", 'mw-taxonomy' )
		);
		parent::__construct( 'custom_tax', esc_html__('Custom Taxonomy List', 'mw-taxonomy' ), $widget_opts );
	}

	function widget( $args, $instance ){
		static $first_dropdown = true;

		$custom_taxonomies = $this->get_custom_taxonomies();
		print $args['before_widget'];
		if( isset( $instance['title'] ) ){
			$title = $instance['title'];
		}
		elseif( is_array( $custom_taxonomies ) ){
			if( isset( $custom_taxonomies[$instance['taxonomy']]['name'] ) ){
				$title = $custom_taxonomies[$instance['taxonomy']]['name'];
			}
		}
		else{
			$title = 'Custom Taxonomy';
		}

		if( isset( $instance['taxonomy'] ) ){
			$selected_taxonomy = $instance['taxonomy'];
		}
		else{
			$selected_taxonomy = 'category';
		}

		$show_count = false;
		if( isset( $instance['count'] ) ){
			$show_count = true;
		}

		$cat_args = array(
			'orderby'      => 'name',
			'show_count'   => $show_count,
			'taxonomy' => $selected_taxonomy,
			'title_li' => ''
		);
		print $args['before_title'] . $title. $args['after_title'];

		if( isset( $instance['dropdown'] ) and $instance['dropdown'] == true ){
			$dropdown_id = ( $first_dropdown ) ? $selected_taxonomy : "{$this->id_base}-dropdown-{$this->number}";
			$first_dropdown = false;

			echo '
<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . $title . '</label>
';
			if( isset( $custom_taxonomies[$selected_taxonomy]['singular_name'] ) ){
				$cat_args['show_option_none'] = sprintf( __( 'Select %s', 'mw-taxonomy' ), $custom_taxonomies[$selected_taxonomy]['singular_name'] );
				$cat_args['id'] = $dropdown_id;
				$cat_args['value_field'] = 'slug';
			}

			/**
			 * Filter the arguments for the Categories widget drop-down.
			 *
			 * @since 2.8.0
			 *
			 * @see wp_dropdown_categories()
			 *
			 * @param array $cat_args An array of Categories widget drop-down arguments.
			 */
			wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $cat_args ) );
			?>

<script type='text/javascript'>
/* <![CDATA[ */
(function() {
	var dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id ); ?>" );
	function onCatChange() {
		if ( dropdown.options[ dropdown.selectedIndex ].value != -1 ) {
			location.href = "<?php echo home_url(); ?>/?<?php echo $selected_taxonomy; ?>=" + dropdown.options[ dropdown.selectedIndex ].value;
		}
	}
	dropdown.onchange = onCatChange;
})();
/* ]]> */
</script>

<?php
		}
		else{
?>
		<ul>
<?php
		$cat_args['title_li'] = '';

		/**
		 * Filter the arguments for the Categories widget.
		 *
		 * @since 2.8.0
		 *
		 * @param array $cat_args An array of Categories widget options.
		 */
		wp_list_categories( apply_filters( 'widget_categories_args', $cat_args ) );
?>
		</ul>
<?php
		}
		print $args['after_widget'];
	}

	function form( $instance ) {

		$current_taxonomy = $this->_get_current_taxonomy($instance);
		$taxonomies = $this->get_custom_taxonomies();
		$n_tax = count( $taxonomies );
		$taxonomy_id = $this->get_field_id( 'taxonomy' );
		$taxonomy_name = $this->get_field_name( 'taxonomy' );
		if( $n_tax == 0 ){
			print '<p>No custom taxonomy is set</p>';
		}
		elseif( $n_tax == 1 ){
			$tax_slug = key( $taxonomies );
			print '<input type="hidden" id="' . $taxonomy_id . '" name="' . $taxonomy_name . '" value="' . $tax_slug . '">';
		}
		else{
			printf(
				'<p><label for="%1$s">%2$s</label>' .
				'<select class="widefat" id="%1$s" name="%3$s">',
				$taxonomy_id,
				sprintf( __( 'Custom Taxonomy', 'mw-taxonomy' ), ':' ),
				$taxonomy_name
			);
			$first_tax = true;
			foreach ( $taxonomies as $slug => $taxonomy ) {
				print "<!--$slug: $taxonomy[hierarchical]-->";
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $slug ),
					selected( $slug, $current_taxonomy, false ),
					$taxonomy['name']
				);
				if( $first_tax ){
					$first_tax = false;
				}
			}
			echo '</select></p>';
		}
		// from class-wp-widget-categories.php
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = sanitize_text_field( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p>

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

		</p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['taxonomy'] = stripslashes($new_instance['taxonomy']);
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}

	function get_custom_taxonomies(){
		$tax = get_option(' mw_taxonomy' );
		return $tax;
	}

	function _get_current_taxonomy($instance) {
		if ( !empty($instance['taxonomy']) && taxonomy_exists($instance['taxonomy']) )
			return $instance['taxonomy'];

		return '';
	}

}
?>
