<?php
/**
 * Modifies the text (HTML) displayed at the top of each flexible content layout; this is how all of the front-end stuff
 * gets put in to the post edit screen and we subsequently get a WYSIWYG experience. It was after seeing this filter
 * put a simple text string in to the flexible header that the idea of creating a full blown WYSIWYG editor came from,
 * and here we are :-)
 *
 * @link https://www.advancedcustomfields.com/resources/acf-fields-flexible_content-layout_title/
 *
 * @since 1.0
 * @version 1.0
 */
function wp_blocks_flexible_layout_title( $title, $field, $layout, $i ) {
	
	// remove layout title from text
	$title = '';

	if( get_row_layout() == 'hero' ) {
	
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
		if( $txt ) {

			$title .= '<div class="wpb-hero';

				// If a gradient has been been selected
				if( $gradient ) {

					// Output the relevant class
					$title .= ' wpb-gradient-' . esc_html( $gradient ) . '';

				}

				// If the 'class' field has text in it
				if( $class ) {

					// Echo the 'class' field
					$title .= ' ' . esc_html( $class ) . '';

				}

				$title .= '"';

				// If the 'id' field has text in it
				if( $id ) {

					// Echo the 'id' field
					$title .= ' id="' . esc_html( $id ) . '" ';

				}

				$title .= 'style="';

				// If an image has been chosen
				if( $background ) {

					// Echo image URL 
					$title .= 'background-image:url(' . esc_html( $background ) . ');';

					// If an image has been chosen
					if( $img_y_top || $img_y_bottom ) {

						// Echo background image align (y)
						$title .= 'background-position-y:' . esc_html( $img_y ) . ';';

					}

				}

				// if '$back_col' has a value and it doesn't equal '#5796da' (default value)
				if( $back_col && $back_col !== '#5796da' ) {

					// Echo colour
					$title .= 'background-color:' . esc_html( $back_col ) . ';';

				}

				$title .= '">';

				// If the user wants to set opacity
				if( $if_opacity ) {

					$title .= '<div class="wpb-hero__opacity" style="opacity:.' . esc_html( $opacity ) . ';-ms-filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=' . esc_html( $opacity ) . '0);';

						// if '$opac_col' has a value and it doesn't equal '#ffffff' (default value)
						if( $opac_col && $opac_col !== '#ffffff' ) {

							$title .= 'background-color:' . esc_html( $opac_col ) . ';'; // Echo colour

						}

					$title .= '"></div>';

				}

				$title .= '<div class="wpb-hero__content-container" style="text-align:' . esc_html( $txt_align ) . '">';

					// If the user wants text to be heading
					if( $if_heading ) {

						$title .= '<h' . esc_html( $heading_level ) . ' class="heading--wpb-hero';

					// Otherwise echo <p
					} else {

						$title .= '<p class="p--wpb-hero';

					}

					$title .= '"';

					// if '$txt_colour' has a value and it doesn't equal '#ffffff' (default value)
					if( $txt_colour && $txt_colour !== '#ffffff' ) { // If a colour has been chosen

						$title .= ' style="color:' . esc_html( $txt_colour ) . '"';

					}

					// Output the '$txt' field, sanitizing as necessary
					$title .= '>' . wp_kses( $txt, wp_blocks_allowed_html() ) . '';

					// If heading is selected
					if( $if_heading ) {

						// Output '</h' followed by the chosen value (e.g. </h2)
						$title .= '</h' . esc_html( $heading_level ) . '';

					// Otherwise echo </p
					} else {

						$title .= '</p';

					}

					$title .= '>';


					/**
					 * if '$link' is an array (which I'm guessing it always will be as it returns 3 values) and a URL is selected, then do this.
					 * There was an issue with some gibberish about an illegal string offset, hence needing the array condition, this
					 * @link https://wordpress.stackexchange.com/a/257347/115004 helped solve it.
					 */
					if ( is_array( $link ) && $link['url'] ) {

						$title .= '<p class="p--wpb-hero--btn-wrapper"><a href="' . esc_url($link['url']) . '"';

						// if the 'target' option has a value
						if ($link['target']) {

							$title .= ' target="' . esc_html($link['target']) . '"';

						}

						$title .= ' class="a--wpb-hero">' . esc_html($link['title']) . '</a></p>'; // Echo the label

					}

				$title .= '</div>';

			$title .= '</div>';

		} else {

			$title .= '<div class="wp-blocks-placeholder"><div class="wp-blocks-placeholder__content">Add a Hero :-)</div></div>';

		}

	}

	// return
	return $title;

}
// name
add_filter('acf/fields/flexible_content/layout_title/name=wp-blocks', 'wp_blocks_flexible_layout_title', 10, 4);

?>