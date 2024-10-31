<?php

class MW_taxonomy_view{

	function info(){ ?>
<div>

<p style="width:150px;float:left;"><a href="https://wordpress.org/support/plugin/mw-taxonomy"  target="_blank" class="page-title-action">
<?php _e( 'Support forum', 'mw-taxonomy' ); ?></a></p>
</div><?php
	}

	function wrap( $h1, $link = '' ){
		?>
<div class="wrap">
<h1><?php
		print $h1;
		if( $link == 'new' ){
			$url = 'admin.php?page=mw-taxonomy&amp;mw_taxonomy_action=new_form';
			$wp_nonce_url = wp_nonce_url(  $url , 'mw_taxonomy_form', 'mw_taxonomy_field' );?>
 <a href="<?php print $wp_nonce_url; ?>" class="page-title-action"><?php
			_e( 'Add New', 'mw-taxonomy' ); ?></a><?php
		}
		elseif( $link == 'start' ){
			$url = 'admin.php?page=mw-taxonomy';
			$wp_nonce_url = wp_nonce_url(  $url , 'mw_taxonomy_form', 'mw_taxonomy_field' );?>
 <a href="<?php print $wp_nonce_url; ?>" class="page-title-action"><?php
			_e( 'Start page', 'mw-taxonomy' ); ?></a><?php
		}
		?></h1>
<?php
	}

	function end_div( $comment = '' ){
		?>
</div>
<?php if( $comment ) print " <!-- $comment -->";
	}

	function message( $mess ){ ?>
<div id="message" class="updated"><p><?php print $mess; ?></p></div><?php
	}

	function table( $list_table ){ ?>
<div><?php $list_table->display(); ?></div><?php
	}

	function form( $action, $form_values, $builtin_post_types, $custom_post_types, $button_value, $mode, $update_slug, $message ){
//		print "mode: $mode<br>";
		if( $mode == 'new_form' ){
?><h2><?php _e( 'Please fill in the form', 'mw-taxonomy' ); ?></h2>
<?php } ?>
<form method="post" action="">
<?php wp_nonce_field( 'mw_taxonomy_form', 'mw_taxonomy_field' ) ?>

<input type="hidden" name="mw_taxonomy_action" value="<?php print $action; ?>">
<input type="hidden" name="mw_taxonomy_existing_slug" value="<?php print $update_slug; ?>">

<table class="form-table">
<tr>
<th scope="row"><label for="mw_taxonomy_name"><?php _e( 'Name', 'mw-taxonomy' ); ?></label><br>
<span style="font-size:12px; font-style: italic;font-weight: normal;"><?php _e( 'The name usually in plural as it will appear on the site and admin area. Ex Categories, Tags', 'mw-taxonomy' ); ?>
</th>
<td style="vertical-align: text-top; padding-top:20px;">
<input required name="mw_taxonomy_name" type="text" id="mw_taxonomy_name" class="regular-text"
value="<?php print $form_values['name']; ?>"></td>
</tr>

<tr>
<th scope="row">
<label for="mw_taxonomy_singular_name"><?php _e( 'Name singular', 'mw-taxonomy' ); ?></label><br>
<span style="font-size:12px; font-style: italic;font-weight: normal;">
<?php _e( 'The name in singular as it will appear on the site and admin area. Ex Category, Tag', 'mw-taxonomy' ); ?>
</span></th>
<td style="vertical-align: text-top; padding-top:20px;">
<input required name="mw_taxonomy_singular_name" type="text" id="mw_taxonomy_singular_name"
class="regular-text" value="<?php if( isset( $form_values['singular_name'] ) ) print $form_values['singular_name']; ?>"></td>
</tr>

<tr>
<th scope="row">
<label for="mw_taxonomy_slug"><?php _e( 'Slug', 'mw-taxonomy' ); ?></label>

<?php
		if( $mode == 'new_form' ){
?>
<br><span style="font-size:12px; font-style: italic;font-weight: normal;">
<?php _e( 'The slug will be used in url', 'mw-taxonomy' ); ?>.
<?php _e( 'May only contain lowercase letters a - z, 0 - 9 or the underscore character and not be more than 32 characters long', 'mw-taxonomy' ); ?>.
<?php _e( 'It cannot be changed once it is set', 'mw-taxonomy' ); ?>.
</span></th>
<td style="vertical-align: text-top; padding-top:20px;">
<input required name="mw_taxonomy_slug" type="text" maxlength=32 minlength=2 id="mw_taxonomy_slug"
class="regular-text" value="<?php if( isset( $form_values['slug'] ) ) print $form_values['slug']; ?>">

<?php }
			else{ ?>
</th><td><?php print $update_slug; ?>
<?php } ?>

</td>
</tr>
<tr>
<th scope="row"><label for="mw_taxonomy_post_type"><?php _e( 'Select Post Types', 'mw-taxonomy' ); ?></label><br>
<span style="font-size:12px; font-style: italic;font-weight: normal;">
<?php _e( 'Select the post types where this taxonomy will be used.', 'mw-taxonomy' ); ?>
</span></th>
<td><?php if( $n_custom_post_types = count( $custom_post_types ) ){ ?>

<b><?php _e( 'Built-in', 'mw-taxonomy' ); ?></b><?php
					} ?>
<ul>
<?php

		foreach ( $builtin_post_types as $post_type ) {

			if( $post_type->name == 'post' or $post_type->name == 'page' ){

		?>
<li style="font-size: 14px;">
<label>
<input type="checkbox" style="margin:1px;" name="mw_taxonomy_post_type[]" value="<?php print $post_type->name; ?>"<?php

			if( isset( $form_values['post_type'] ) ){
				if( is_array( $form_values['post_type'] ) ){
					if( in_array( $post_type->name, $form_values['post_type'] ) ){
				?> checked<?php
					}
				}
			}
			?>>
<?php print $post_type->label; ?></label>
</li>
<?php
			}
		}
?>
</ul><?php

			if( $n_custom_post_types ){?>

<b><?php _e( 'Custom', 'mw-taxonomy' ); ?></b>
<ul>
<?php

				foreach ( $custom_post_types as $post_type ) {

		?>
<li style="font-size: 14px;">
<label>
<input type="checkbox" style="margin:1px;" name="mw_taxonomy_post_type[]" value="<?php print $post_type->name; ?>"<?php


					if( isset( $form_values['post_type'] ) and is_array( $form_values['post_type'] ) ){
						if( in_array( $post_type->name, $form_values['post_type'] ) ){
							?> checked<?php
						}
					} ?>>
<?php print $post_type->label; ?></label>
</li>
<?php
				} ?>
</ul><?php
			}?>
</td>
</tr>

<tr>
<th scope="row"><label for="mw_taxonomy_hierarchical"><?php _e( 'Hierarchical', 'mw-taxonomy' ); ?></label><br>
<span style="font-size:12px; font-style: italic;font-weight: normal;">
<?php _e( 'Whether one can be parent to another.', 'mw-taxonomy' ); ?>
<?php _e( 'Categories are hierarchical and tags are not.', 'mw-taxonomy' ); ?>
</span</th>
<td>
<input type="radio" name="mw_taxonomy_hierarchical" value="Yes"<?php
			if( isset( $form_values['hierarchical'] ) and  $form_values['hierarchical'] == "Yes" ) print " checked"; ?>>
			<?php _e( 'Yes', 'mw-taxonomy' ); ?><br>
<input type="radio" name="mw_taxonomy_hierarchical" value="No"<?php
			if( isset( $form_values['hierarchical'] ) and $form_values['hierarchical'] == "No" ) print " checked"; ?>>
			<?php _e( 'No', 'mw-taxonomy' ); ?>
</td>
</tr>

<tr>
<th scope="row" style="vertical-align: text-top;  width:300px;">
<label for="mw_taxonomy_index"><?php _e( 'Search engine', 'mw-taxonomy' ); ?></label><br>
<span style="font-size:12px; font-style: italic;font-weight: normal;">
<?php _e( 'Yes if you want taxonomy url to appear in search engines', 'mw-taxonomy' ); ?>.
<?php _e( 'No if you want to stop search engines from including taxonomy url', 'mw-taxonomy' ); ?>.
</span
</th>
<td style="vertical-align: text-top; padding-top:5px;">
<!-- form_values[index]: <?php print $form_values['index']; ?> -->
<input type="radio" name="mw_taxonomy_index" value="Yes"<?php
			if( ! isset( $form_values['index'] ) ){
				print " checked"; ?>><?php _e( 'Yes', 'mw-taxonomy' ); ?><br>
				<input type="radio" name="mw_taxonomy_index" value="No" <?php
			}
			else{
				if( $form_values['index'] == "Yes" ){
					print " checked";
				}
				?>><?php _e( 'Yes', 'mw-taxonomy' ); ?><br>
				<input type="radio" name="mw_taxonomy_index" value="No"<?php

				if( $form_values['index'] == "No" ){
					print " checked";
				}
			}
			?>><?php _e( 'No', 'mw-taxonomy' ); ?>
</td>
</tr>

</table>
<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php print $button_value; ?>"></p>
</form>
<?php
	}

	function no_tax(){
		?>
<div style="margin:20px 0 50px 0;"><p><?php	_e( 'You have no Custom Taxonomy yet', 'mw-taxonomy' ); ?></p><?php
		$url = 'admin.php?page=mw-taxonomy&amp;mw_taxonomy_action=new_form';
		$wp_nonce_url = wp_nonce_url(  $url , 'mw_taxonomy_form', 'mw_taxonomy_field' );?>
<p><a href="<?php print $wp_nonce_url; ?>" class="page-title-action"><?php
		_e( 'Create one now', 'mw-taxonomy' ); ?></a>
</p></div><?php
	}

	function warning_page( $form_values, $update_slug ){
		?>

<form method="post" action="">
<?php wp_nonce_field( "mw_taxonomy_form", 'mw_taxonomy_field' ) ?>

<input type="hidden" name="mw_taxonomy_action" value="really_delete">
<input type="hidden" name="mw_taxonomy_slug" value="<?php print $update_slug; ?>">
<table class="form-table">
<tr>
<th scope="row"><?php _e( 'Taxonomy Name (plural)', 'mw-taxonomy' ); ?></th>
<td><?php print $form_values['name']; ?></td>
</tr>
<tr>
<th scope="row"><?php _e( 'Taxonomy Name (singular)', 'mw-taxonomy' ); ?></th>
<td><?php print $form_values['singular_name']; ?></td>
</tr>
<!--
<tr>
<th scope="row"><?php _e( 'Description', 'mw-taxonomy' ); ?></th>
<td><?php print $form_values['description']; ?></td>
</tr>
-->
<tr>
<th scope="row"><?php _e( 'Post type', 'mw-taxonomy' ); ?></th>
<td><?php foreach( $form_values['post_type'] as $post_type ) print $post_type."<br>"; ?></td>
</tr>
<tr>
<th scope="row"><?php _e( 'Hierarchical', 'mw-taxonomy' ); ?></th>
<td><?php print $form_values['hierarchical']; ?></td>
</tr>
</table>
<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Really Delete', 'mw-taxonomy' ); ?>"></p>
</form>
<?php
	}

}

?>
