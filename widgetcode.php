<?php
add_action('widgets_init', 'foxyshop_load_widgets');

function foxyshop_load_widgets() {
	register_widget('FoxyShop_Category');
	register_widget('FoxyShop_Cart_Link');
}


class FoxyShop_Category extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function FoxyShop_Category() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'foxyshop_category', 'description' => __('Show the contents of a FoxyShop product category.') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'foxyshop-category-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'foxyshop-category-widget', __('FoxyShop Category'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$categoryName = $instance['categoryName'];
		$showMoreDetails = isset( $instance['showMoreDetails'] ) ? $instance['showMoreDetails'] : false;
		$showAddToCart = isset( $instance['showAddToCart'] ) ? $instance['showAddToCart'] : false;
		$showMax = ($instance['showMax'] > 0 ) ? $instance['showMax'] : -1;

		echo $before_widget;
		if ($title) echo $before_title . $title . $after_title;

		if ($instance['simpleView']) {
			echo '<div class="foxyshop_category_simple_widget">';
			foxyshop_featured_category($categoryName, $showAddToCart, $showMoreDetails, $showMax, True);
			echo '</div>';
		} else {
			echo '<div class="foxyshop_category_widget">';
			foxyshop_featured_category($categoryName, $showAddToCart, $showMoreDetails, $showMax);
			echo '</div>';
		}		
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['categoryName'] = strip_tags( $new_instance['categoryName'] );
		$instance['showMax'] = (int)strip_tags( $new_instance['showMax'] );

		/* No need to strip tags */
		$instance['simpleView'] = $new_instance['simpleView'];
		$instance['showAddToCart'] = $new_instance['showAddToCart'];
		$instance['showMoreDetails'] = $new_instance['showMoreDetails'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => "",
			'categoryName' => "",
			'showAddToCart' => "",
			'showMoreDetails' => "on",
			'simpleView' => "",
			'showMax' => -1
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:260px;" />
		</p>

		<!-- Select Category -->
		<p>
			<label for="<?php echo $this->get_field_id( 'categoryName' ); ?>"><?php _e('Category:'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'categoryName' ); ?>" name="<?php echo $this->get_field_name( 'categoryName' ); ?>" class="widefat" style="width:100%;">
				<option value="">- - Select Category - -</option>
				<?php
				$toplevelterms = get_terms('foxyshop_categories', 'hide_empty=0&hierarchical=0');
				$arrCategory = array();
				foreach ($toplevelterms as $toplevelterm) {
					echo '<option value="' . $toplevelterm->slug .'"';
					if ($instance['categoryName'] == $toplevelterm->slug) echo ' selected="selected"';
					echo '>' . str_replace("_","",$toplevelterm->name) . '</option>';
				}
				?>
			</select>
		</p>

		<!-- Max Entries -->
		<p>
			<label for="<?php echo $this->get_field_id( 'showMax' ); ?>"><?php _e('Max Products to Show:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'showMax' ); ?>" name="<?php echo $this->get_field_name( 'showMax' ); ?>" value="<?php echo ($instance['showMax'] != 0 ? $instance['showMax'] : ''); ?>" style="width:50px;" /> <span class="small">(optional)</span>
		</p>


		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['simpleView'], 'on' ); ?> id="<?php echo $this->get_field_id( 'simpleView' ); ?>" name="<?php echo $this->get_field_name( 'simpleView' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'simpleView' ); ?>"><?php _e('Show Simple View'); ?></label>
		</p>

		<!-- Show Checkboxes for Button Selection -->
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['showAddToCart'], 'on' ); ?> id="<?php echo $this->get_field_id( 'showAddToCart' ); ?>" name="<?php echo $this->get_field_name( 'showAddToCart' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'showAddToCart' ); ?>"><?php _e('Show Add To Cart Button'); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['showMoreDetails'], 'on' ); ?> id="<?php echo $this->get_field_id( 'showMoreDetails' ); ?>" name="<?php echo $this->get_field_name( 'showMoreDetails' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'showMoreDetails' ); ?>"><?php _e('Show More Details Button'); ?></label>
		</p>


	<?php
	}
}




class FoxyShop_Cart_Link extends WP_Widget {

	function FoxyShop_Cart_Link() {
		$widget_ops = array( 'classname' => 'foxyshop_cart_link', 'description' => __('Show a link to view shopping cart.') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'foxyshop-cart-link-widget' );
		$this->WP_Widget( 'foxyshop-cart-link-widget', __('FoxyShop Cart Link'), $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$linkText = $instance['linkText'];
		$hideEmpty = isset( $instance['hideEmpty'] ) ? $instance['hideEmpty'] : false;

		echo $before_widget;
		if ($title) echo $before_title . $title . $after_title;

		echo '<ul class="foxyshop_cart_link_widget"><li>';
		foxyshop_cart_link($linkText, $hideEmpty);
		echo '</li></ul>';
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		/* No need to strip tags */
		$instance['linkText'] = $new_instance['linkText'];
		$instance['hideEmpty'] = $new_instance['hideEmpty'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => "",
			'linkText' => "",
			'hideEmpty' => ""
		);
		$instance = wp_parse_args((array)$instance, $defaults); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:260px;" />
		</p>

		<!-- Max Entries -->
		<p>
			<div><?php _e('Link Text:'); ?></div>
			<textarea id="<?php echo $this->get_field_id( 'linkText' ); ?>" name="<?php echo $this->get_field_name( 'linkText' ); ?>" style="width: 100%;"><?php echo $instance['linkText']; ?></textarea>
			<span class="small">Example: View Cart (%q% Items) ($%p%)</span>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['hideEmpty'], 'on' ); ?> id="<?php echo $this->get_field_id( 'hideEmpty' ); ?>" name="<?php echo $this->get_field_name( 'hideEmpty' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'hideEmpty' ); ?>"><?php _e('Hide Link if Cart is Empty'); ?></label>
		</p>

	<?php
	}
}
?>