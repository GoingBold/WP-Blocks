<?php
/**
 * Partial that displays the content for the 'Hero' block
 *
 *
 * @package wp_blocks
 * @since 1.0
 * @version 1.0
 */

// vars (yep, there's almost 20 of them...)
$txt = get_sub_field('txt', false, false);
$txt_align = get_sub_field('txt-align');
$txt_colour = get_sub_field('txt-col');
$if_heading = get_sub_field('if-heading');
$heading_level = get_sub_field('heading');
$class = get_sub_field('class');
$id = get_sub_field('id');
$link = get_sub_field('url');
$background = get_sub_field('img');
$back_col = get_sub_field('back-col');
$img_y = get_sub_field('img-align-y');
$img_y_top = $img_y == 'top';
$img_y_bottom = $img_y == 'bottom';
$if_opacity = get_sub_field('if-opacity');
$opacity = get_sub_field('opacity');
$opac_col = get_sub_field('opac-col');
$gradient = get_sub_field('grad');

// If there's text in '$txt'
if( $txt ):

	?><div class="wpb-hero<?php

		// If a gradient has been been selected
		if( $gradient ) {

			?> wpb-gradient-<?php echo esc_html( $gradient ); // Output the relevant class

		}

		// If the 'class' field has text in it
		if( $class ) {

			?> <?php echo esc_html( $class ); // Echo the 'class' field

		}

		?>"<?php

		// If the 'id' field has text in it
		if( $id ) {

			?> id="<?php echo esc_html( $id ); // Echo the 'class' field ?>" <?php

		}

		?>style="<?php

		// If an image has been chosen
		if( $background ) {

			?>background-image:url(<?php echo esc_html( $background ); // Echo image URL ?>);<?php

			// If an image has been chosen
			if( $img_y_top || $img_y_bottom ) {

				?>background-position-y:<?php echo esc_html( $img_y ); // Echo background image align (y) ?>;<?php

			}

		}

		// if '$back_col' has a value and it doesn't equal '#5796da' (default value)
		if( $back_col && $back_col !== '#5796da' ) {

			?>background-color:<?php echo esc_html( $back_col ); // Echo colour ?>;<?php

		}

		?>"><?php

		// If the user wants to set opacity
		if( $if_opacity ) {

			?><div class="wpb-hero__opacity" style="opacity:.<?php echo esc_html( $opacity ); // Output opacity value ?>;-ms-filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=<?php echo esc_html( $opacity ); // Output opacity value ?>0);<?php

				// if '$opac_col' has a value and it doesn't equal '#ffffff' (default value)
				if( $opac_col && $opac_col !== '#ffffff' ) {

					?>background-color:<?php echo esc_html( $opac_col ); // Echo colour ?>;<?php

				}

			?>"></div><?php

		}

		?><div class="wpb-hero__content-container" style="text-align:<?php echo esc_html( $txt_align ); ?>"><?php

		// If the user wants text to be heading
		if( $if_heading ) {

				?><h<?php echo esc_html( $heading_level ); // Output '<h' followed by the chosen value (e.g. <h2) ?> class="heading--wpb-hero<?php

			// Otherwise echo <p
			} else {

				?><p class="p--wpb-hero<?php

			}

			?>"<?php

			// if '$txt_colour' has a value and it doesn't equal '#ffffff' (default value)
			if( $txt_colour && $txt_colour !== '#ffffff' ) { // If a colour has been chosen

				?> style="color:<?php echo esc_html( $txt_colour ); // Echo HEX value ?>"<?php

			}

			?>><?php echo wp_kses( $txt, wp_blocks_allowed_html() ); // Output the '$txt' field, sanitizing as necessary

			// If heading is selected
			if( $if_heading ) {

				?></h<?php echo esc_html( $heading_level ); // Output '</h' followed by the chosen value (e.g. </h2)

			// Otherwise echo </p
			} else {

				?></p<?php

			}

			?>><?php

			/**
			 * if '$link' is an array (which I'm guessing it always will be as it returns 3 values) and a URL is selected, then do this.
			 * There was an issue with some gibberish about an illegal string offset, hence needing the array condition, this
			 * @link https://wordpress.stackexchange.com/a/257347/115004 helped solve it.
			 */
			if ( is_array( $link ) && $link['url'] ) {

				?><p class="p--wpb-hero--btn-wrapper"><a href="<?php echo esc_url($link['url']); // Echo URL ?>"<?php

				// if the 'target' option has a value
				if ($link['target']) {

					?> target="<?php echo esc_html($link['target']); ?>"<?php

				}

				?> class="a--wpb-hero"><?php echo esc_html($link['title']); // Echo the label ?></a></p><?php

			}

		?></div><?php

	?></div><?php

endif;

?>