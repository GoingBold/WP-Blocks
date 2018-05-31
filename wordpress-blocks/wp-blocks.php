<?php
/*
Plugin Name: WP Blocks
Plugin URI: https://github.com/GoingBold/WP-Blocks
Description: WP Blocks is a full WYSIWYG content management solution for WordPress.
Version: 1.0.0
Author: Going Bold
Author URI: https://goingbold.co.uk/
Text Domain: wp-blocks
License: GPL2
*/

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists('wp_blocks') ) :

	class wp_blocks {

		/**
		 * Adds register hooks.
		 */
		public function __construct() {

			// vars
			$this->settings = array(
				'plugin'			=> 'WP Blocks',
				'this_acf_version'	=> 0,
				'min_acf_version'	=> '5.6.0',
				'version'			=> '1.0.0',
				'url'				=> plugin_dir_url( __FILE__ ),
				'path'				=> plugin_dir_path( __FILE__ ),
			);

			// Set text domain
			load_plugin_textdomain( 'wp-blocks', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );

			// Add the 'acf_or_die' function (defined below) that checks if ACF is installed
			add_action( 'admin_init', array($this, 'acf_or_die'), 11);

			// Adds some CSS to the admin pages that ACF is on
			add_action('acf/input/admin_enqueue_scripts', array( &$this, 'wp_blocks_admin_scripts_styles' ), false, '1.0.0' );

			// Adds CSS for the hero to the front-end
			add_action( 'wp_enqueue_scripts', array($this, 'wp_blocks_enqueue_styles') );

			// On 'ACF Save Post', save our blocks to post content.
			add_action('acf/save_post', array($this, 'wp_blocks_save_blocks_to_content'), 20);

			/**
			 * Admin notices , includes 2, the first displays if restoring from a revision
			 * and the second displays if there's content in the_content() and no WP Blocks
			 */
			add_action('admin_notices', array($this, 'wp_blocks_save_custom_admin_notices'), 20);

			// Call function for sanitizing WYSIWYG field
			add_action( 'init', array($this, 'wp_blocks_init'), 11 );

			// Call function to display WYSIWYG on post edit screen
			add_action('init', array($this, 'wp_blocks_hero_post_edit'), 99 );

			// Call function for custom field distribution
			add_action('acf/init', array( &$this, 'wp_blocks_field_dist' ) );

		}




		/**
		 * Let's make sure ACF Pro is installed & activated
		 * If not, we give notice and kill the activation of WP Blocks.
		 * Also works if ACF Pro is deactivated.
		 *
		 * @since 1.0
		 * @version 1.0
		 */
		function acf_or_die() {

			// If the 'acf' class (defined by ACF) isn't installed then do this.
			if ( (!class_exists('acf')) ) {
				$this->kill_plugin();
			// Otherwise check the version of ACF.
			} else {
				$this->settings['this_acf_version'] = acf()->settings['version'];
				if ( version_compare( $this->settings['this_acf_version'], $this->settings['min_acf_version'], '<' ) ) {
					$this->kill_plugin();
				}
			}
		}


		/**
		 * Deactivate plugin if ACF isn't installed (or ver too old).
		 *
		 * @since 1.0
		 * @version 1.0
		 */
		function kill_plugin() {
			deactivate_plugins( plugin_basename( __FILE__ ) );   
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			add_action( 'admin_notices', array($this, 'acf_dependent_plugin_notice') );
		}

		/**
		 * Display notice.
		 *
		 * @since 1.0
		 * @version 1.0
		 */
		function acf_dependent_plugin_notice() {
			if (!class_exists('acf')) {
				echo '<div class="error"><p>' . sprintf( __('%1$s requires ACF PRO v%2$s or higher to be installed and activated.', 'wp_blocks'), $this->settings['plugin'], $this->settings['min_acf_version']) . '</p></div>';
			}
		}




		/**
		 * Puts CSS in the head of ACF admin pages.
		 *
		 * @link https://wordpress.stackexchange.com/a/94952/115004
		 * @link https://www.advancedcustomfields.com/resources/acf-input-admin_enqueue_scripts/
		 *
		 * @since 1.0
		 * @version 1.0
		 */
		function wp_blocks_admin_scripts_styles() {

			$theme = wp_get_theme(); // gets the current theme

			// If the 'Campaign Pro' theme is active or if it's the parent theme to this one
			// then return empty (as the styles are included with the theme)
			if ( 'Campaign Pro' == $theme->name || 'Campaign Pro' == $theme->parent_theme ) {

				// empty

			// Otherwise return the styles
			} else {

				// WP Blocks WYSIWYG styles (used on the front-end and back-end)
				wp_register_style( 'wp-blocks-wysiwyg-styles', plugins_url( '/css/style.css', __FILE__ ) );
				wp_enqueue_style( 'wp-blocks-wysiwyg-styles' );

				// WP Blocks core styles (used to style post edit screen)
				wp_register_style( 'wp-blocks-core-styles', plugins_url( '/css/admin/wp-blocks-core-styles.css', __FILE__ ) );
				wp_enqueue_style( 'wp-blocks-core-styles' );

				// register script
				wp_register_script( 'wp-blocks-core-js', plugins_url( '/assets/js/admin/wp-blocks-core.js', __FILE__ ) );
				wp_enqueue_script( 'wp-blocks-core-js' );

			}

		}



		/**
		 * Enqueues styles for front-end
		 *
		 * @since 1.0
		 * @version 1.0
		 */
		function wp_blocks_enqueue_styles() {
			
			// WP Blocks WYSIWYG styles (used on the front-end and back-end)
			wp_register_style( 'wp-blocks-wysiwyg-styles', plugins_url( '/css/style.css', __FILE__ ) );
			wp_enqueue_style( 'wp-blocks-wysiwyg-styles' );

		}




		/**
		 * Save ACF Flexible Content (WP Blocks) to post content.
		 *
		 * Based on @link https://gist.github.com/AaronRutley/c0a49d89daf92ad75b6a6112f6722f38
		 * and @link https://wordpress.stackexchange.com/a/220423
		 *
		 * @since 1.0
		 * @version 1.0
		 */ 
		function wp_blocks_save_blocks_to_content( $post_id ) {

			$theme = wp_get_theme(); // gets the current theme

			// If the 'Campaign Pro' theme is active or if it's the parent theme to this one
			// then return empty (as the styles are included with the theme)
			if ( 'Campaign Pro' == $theme->name || 'Campaign Pro' == $theme->parent_theme ) {

				// empty

			// Otherwise return the styles
			} else {

				// Only run this code if we're on a particular post type
				if ( 'post' == get_post_type() || 'page' == get_post_type() ) {

					// If there are any blocks
					if( get_field('wp-blocks') ) {

						// Start an output buffer
						ob_start();
						
						// check if the flexible content field has rows of data
						if( have_rows('wp-blocks') ):

							// loop through the rows of data
							while ( have_rows('wp-blocks') ) : the_row();

								// If the 'Hero' layout/block is present
								if( get_row_layout() == 'hero' ):

									// Get the file /inc/partials/hero.php (which contains the relevant block layouts)
									include( 'inc/partials/hero-front-end.php' );

								endif;

							endwhile;

						endif;

						// Store output buffer
						$new_post_content = ob_get_clean();

						// Remove post revisions (as there was an issue with 2 revisions being created)
						remove_action( 'post_updated', 'wp_save_post_revision' );

						// Update the post_content 
						wp_update_post( array('ID' => $post_id, 'post_content' => $new_post_content ));

						// Re-add post revisions
						add_action( 'post_updated', 'wp_save_post_revision' );

					} else {

						return;

					}

				}

			}

		}




		/**
		 * Because we're disabling a revision when ACF fields are copied over to 'the_content()'
		 * (so 2 revisions aren't created every time a post is saved), if a revision is
		 * restored it may only return the ACF fields and not 'the_content()'. This message
		 * simply highlights to the user to click 'Update' if they need to.
		 *
		 * @since 1.0
		 * @version 1.0
		 */ 
		function wp_blocks_save_custom_admin_notices() {

			// If 'revision' is in the address bar and we're on a published post
			if (isset($_GET['revision']) && get_post_status () == 'publish') {

		?>

				<div class="notice notice-info is-dismissible">
					<p><?php _e('After restoring a revision click <span class="button button-primary">Update</span> to refresh the Blocks. <a href="#" class="button">Find Out More</a>', 'wp-blocks'); ?></p>
				</div>

		<?php

			}

			// If ACF is active
			if ( (class_exists('acf')) ) {

				// If this is a post, page, campaign or event and 'post' is in the address bar (basically checks we're on the post edit screen)
				if ('post' == get_post_type() || 'page' == get_post_type() && isset($_GET['post'])) {
					
					// Global post variable
					global $post;
					// Set-up post data as we'll be checking if the_content() is empty
					setup_postdata($post);

					// vars
					$id = $post->ID;
					$thecontent = get_the_content($id);
					$wp_blocks = get_field('wp-blocks');
					$query = get_post(get_the_ID()); 
					$content = apply_filters('the_content', $query->post_content);
				
					/**
					 * If the content isn't empty and there are no WP Blocks - so if for example, a page/post was created, content was added
					 * using the default editor, and then WP Blocks was added, we want to let the user know that WP Blocks saves
					 * content to the_content(), which means that it will replace anything already in the_content(). This message simply
					 * tells the user to back-up the content in the_content(), or make sure revisions are on.
					 *
					 */
					if(!empty($thecontent) && !$wp_blocks) {

			?>

						<div class="notice notice-info is-dismissible">
							<p><?php _e('Looks like this page already has content. When you add and save blocks they will replace the content in the default editor, so if you don\'t have revisions on, back-up your content before saving blocks.  Click \'Show the default editor\' to take a look at the content that will be replaced.', 'wp-blocks'); ?></p><p><button type="button" class="button button-primary button-primary--wpb-show-default-editor"><?php _e('Show the default editor', 'wp-blocks'); ?></button> <a href="https://campaignpro.net/existing-content/" class="button"><?php _e('Find out more', 'wp-blocks'); ?></a></p><p class="help"><?php _e('The default editor will display below WP Blocks.', 'wp-blocks'); ?></p>
						</div>

			<?php

					}

					// Reset the post data
					wp_reset_postdata();

				}

			}

		}



		/**
		 * This function is called after plugins have loaded so anything that needs to happen
		 * once all plugins have loaded goes here.
		 */
		function wp_blocks_init() {

			/**
			 * Function that creates an array of allowed html (used for sanitized output on WYSIWYG field)
			 *
			 * @link https://wp-mix.com/wordpress-basic-allowed-html-wp_kses/
			 *
			 * @since 1.0
			 * @version 1.0
			 */
			function wp_blocks_allowed_html() {

				$allowed_tags = array(
					'a' => array(
						'class' => array(),
						'href'  => array(),
						'rel'   => array(),
						'title' => array(),
					),
					'abbr' => array(
						'title' => array(),
					),
					'b' => array(),
					'blockquote' => array(
						'cite'  => array(),
					),
					'cite' => array(
						'title' => array(),
					),
					'code' => array(),
					'del' => array(
						'datetime' => array(),
						'title' => array(),
					),
					'dd' => array(),
					'div' => array(
						'class' => array(),
						'title' => array(),
						'style' => array(),
					),
					'dl' => array(),
					'dt' => array(),
					'em' => array(),
					'h1' => array(),
					'h2' => array(),
					'h3' => array(),
					'h4' => array(),
					'h5' => array(),
					'h6' => array(),
					'i' => array(),
					'img' => array(
						'alt'    => array(),
						'class'  => array(),
						'height' => array(),
						'src'    => array(),
						'width'  => array(),
					),
					'li' => array(
						'class' => array(),
					),
					'ol' => array(
						'class' => array(),
					),
					'p' => array(
						'class' => array(),
					),
					'q' => array(
						'cite' => array(),
						'title' => array(),
					),
					'span' => array(
						'class' => array(),
						'title' => array(),
						'style' => array(),
					),
					'strike' => array(),
					'strong' => array(),
					'ul' => array(
						'class' => array(),
					),
				);

				return $allowed_tags;
			}

		}


		/**
		 * Calls the file that holds all the gubbins for the WYSIWYG on the post edit screen.
		 */
		function wp_blocks_hero_post_edit() {

			include_once( 'inc/partials/admin/hero-post-edit-screen.php' );

		}




		/**
		 * Custom fields distribution, the is auto generated by ACF when using the
		 * export function (Tools > Select Field Group > Generate PHP)
		 *
		 * @link https://www.advancedcustomfields.com/resources/register-fields-via-php/
		 */
		function wp_blocks_field_dist() {

			if( function_exists('acf_add_local_field_group') ):

				acf_add_local_field_group(array(
					'key' => 'group_5aec56b396efe',
					'title' => 'WP Blocks',
					'fields' => array(
						array(
							'key' => 'field_5aec56b3a04f6',
							'label' => 'Blocks',
							'name' => 'wp-blocks',
							'type' => 'flexible_content',
							'instructions' => 'Use WP Blocks to create any layout you can imagine.',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => 'wp-blocks--content',
								'id' => 'wp-blocks',
							),
							'layouts' => array(
								'5a3d63c08e7d1' => array(
									'key' => '5a3d63c08e7d1',
									'name' => 'hero',
									'label' => 'Hero',
									'display' => 'block',
									'sub_fields' => array(
										array(
											'key' => 'field_5aec56b3a25ab',
											'label' => '',
											'name' => '',
											'type' => 'message',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => 'wp-blocks-settings-intro',
												'id' => '',
											),
											'message' => '<header>
				<h2><span>Customising <span class="dashicons dashicons-arrow-right"></span> WP Blocks<span class="screen-reader-text">: </span></span>Hero</h2>
				</header>',
											'new_lines' => 'wpautop',
											'esc_html' => 0,
										),
										array(
											'key' => 'field_5aec56b3a262c',
											'label' => '<span class="dashicons dashicons-text"></span> Text',
											'name' => '',
											'type' => 'accordion',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'open' => 1,
											'multi_expand' => 1,
											'endpoint' => 0,
										),
										array(
											'key' => 'field_5aec56b3a26a9',
											'label' => 'Content',
											'name' => '',
											'type' => 'tab',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'placement' => 'top',
											'endpoint' => 0,
										),
										array(
											'key' => 'field_5aec56b3a2724',
											'label' => '',
											'name' => 'txt',
											'type' => 'wysiwyg',
											'instructions' => 'Choose something relevant, short and snappy to grab your users attention.',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'default_value' => '',
											'tabs' => 'all',
											'toolbar' => 'basic',
											'media_upload' => 0,
											'delay' => 1,
										),
										array(
											'key' => 'field_5aec56b3a279f',
											'label' => 'Settings',
											'name' => '',
											'type' => 'tab',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'placement' => 'top',
											'endpoint' => 0,
										),
										array(
											'key' => 'field_5aec56b3a281f',
											'label' => 'Text Align',
											'name' => 'txt-align',
											'type' => 'button_group',
											'instructions' => 'Left, center or right aligned?',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'choices' => array(
												'left' => '<span class="dashicons dashicons-editor-alignleft">Left aligned</span>',
												'center' => '<span class="dashicons dashicons-editor-aligncenter">Center aligned</span>',
												'right' => '<span class="dashicons dashicons-editor-alignright">Right aligned</span>',
											),
											'allow_null' => 0,
											'default_value' => 'center',
											'layout' => 'horizontal',
											'return_format' => 'value',
										),
										array(
											'key' => 'field_5aec56b3a28a7',
											'label' => 'Text Colour',
											'name' => 'txt-col',
											'type' => 'color_picker',
											'instructions' => 'Set the text colour (<strong>default: #ffffff</strong>).',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'default_value' => '#ffffff',
										),
										array(
											'key' => 'field_5aec56b3a2937',
											'label' => '<span class="screen-reader-text">Display as heading text?</span>',
											'name' => 'if-heading',
											'type' => 'true_false',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => 'wpb-true-false',
												'id' => '',
											),
											'message' => 'If this is the first block, or is used to start a section, then you\'ll want a heading. If this is midway through content then you may want to use paragraph text instead. N.B. This only changes the HTML that\'s used to structure the content, there is no visible difference.',
											'default_value' => 0,
											'ui' => 1,
											'ui_on_text' => 'Display as heading text',
											'ui_off_text' => 'Display as heading text',
										),
										array(
											'key' => 'field_5aec56b3a29c3',
											'label' => 'Heading Level',
											'name' => 'heading',
											'type' => 'radio',
											'instructions' => 'To help people who use screen readers, it\'s important to use a heading structure that is logically structured. H1 is the first heading on the page, with the rest being H2\'s, H3\'s etc',
											'required' => 0,
											'conditional_logic' => array(
												array(
													array(
														'field' => 'field_5aec56b3a2937',
														'operator' => '==',
														'value' => '1',
													),
												),
											),
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'choices' => array(
												1 => 'H1',
												2 => 'H2',
												3 => 'H3',
												4 => 'H4',
												5 => 'H5',
											),
											'allow_null' => 0,
											'other_choice' => 0,
											'save_other_choice' => 0,
											'default_value' => 2,
											'layout' => 'horizontal',
											'return_format' => 'value',
										),
										array(
											'key' => 'field_5aec56b3a2a44',
											'label' => '',
											'name' => '',
											'type' => 'tab',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'placement' => 'top',
											'endpoint' => 1,
										),
										array(
											'key' => 'field_5aec56b3a2ac0',
											'label' => '',
											'name' => '',
											'type' => 'accordion',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'open' => 1,
											'multi_expand' => 1,
											'endpoint' => 1,
										),
										array(
											'key' => 'field_5aec56b3a2b3b',
											'label' => '<span class="dashicons dashicons-admin-links"></span> Button / Link',
											'name' => '',
											'type' => 'accordion',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'open' => 0,
											'multi_expand' => 1,
											'endpoint' => 0,
										),
										array(
											'key' => 'field_5aec56b3a2bb7',
											'label' => 'Link',
											'name' => 'url',
											'type' => 'link',
											'instructions' => 'Where do you want to link? If it\'s a page/post on this site, just start typing the name of the post/page and it\'ll pop up.',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'return_format' => 'array',
										),
										array(
											'key' => 'field_5aec56b3a2c37',
											'label' => '',
											'name' => '',
											'type' => 'accordion',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'open' => 0,
											'multi_expand' => 1,
											'endpoint' => 1,
										),
										array(
											'key' => 'field_5aec56b3a2cbf',
											'label' => '<span class="dashicons dashicons-format-image"></span> Background',
											'name' => '',
											'type' => 'accordion',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'open' => 0,
											'multi_expand' => 1,
											'endpoint' => 0,
										),
										array(
											'key' => 'field_5aec56b3a2d4e',
											'label' => 'Content',
											'name' => '',
											'type' => 'tab',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'placement' => 'top',
											'endpoint' => 0,
										),
										array(
											'key' => 'field_5aec56b3a2dd1',
											'label' => 'Image',
											'name' => 'img',
											'type' => 'image',
											'instructions' => 'Choose an image for the background of the hero. Psst, choose something awesome!',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'return_format' => 'url',
											'preview_size' => 'medium',
											'library' => 'all',
											'min_width' => '',
											'min_height' => '',
											'min_size' => '',
											'max_width' => '',
											'max_height' => '',
											'max_size' => '',
											'mime_types' => '',
										),
										array(
											'key' => 'field_5aec56b3a2e4c',
											'label' => '...and choose a background colour',
											'name' => 'back-col',
											'type' => 'color_picker',
											'instructions' => 'If you don\'t want to display an image, or your image doesn\'t cover the whole hero frame, then this background colour will display (<strong>default: #5796da</strong>).',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'default_value' => '#5796da',
										),
										array(
											'key' => 'field_5aec56b3a2ec7',
											'label' => 'Settings',
											'name' => '',
											'type' => 'tab',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'placement' => 'top',
											'endpoint' => 0,
										),
										array(
											'key' => 'field_5aec56b3a2f42',
											'label' => 'Gradient',
											'name' => 'grad',
											'type' => 'button_group',
											'instructions' => 'Choose a gradient that will be applied to the background.',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => 'wpb-choose-gradient',
												'id' => '',
											),
											'choices' => array(
												'xxs' => '<span class="xxs">Extra Extra Small</span>',
												'xs' => '<span class="xs">Extra Small</span>',
												's' => '<span class="s">Small</span>',
												'm' => '<span class="m">Medium</span>',
												'l' => '<span class="l">Large</span>',
												'xl' => '<span class="xl">Extra Large</span>',
												'xxs--l-r' => '<span class="xxs--l-r">Extra Extra Small (L-R)</span>',
												'xs--l-r' => '<span class="xs--l-r">Extra Small (L-R)</span>',
												's--l-r' => '<span class="s--l-r">Small (L-R)</span>',
												'm--l-r' => '<span class="m--l-r">Medium (L-R)</span>',
												'l--l-r' => '<span class="l--l-r">Large (L-R)</span>',
												'xl--l-r' => '<span class="xl--l-r">Extra Large (L-R)</span>',
												'xxs--r-l' => '<span class="xxs--r-l">Extra Extra Small (R-L)</span>',
												'xs--r-l' => '<span class="xs--r-l">Extra Small (R-L)</span>',
												's--r-l' => '<span class="s--r-l">Small (R-L)</span>',
												'm--r-l' => '<span class="m--r-l">Medium (R-L)</span>',
												'l--r-l' => '<span class="l--r-l">Large (R-L)</span>',
												'xl--r-l' => '<span class="xl--r-l">Extra Large (R-L)</span>',
											),
											'allow_null' => 1,
											'default_value' => '',
											'layout' => 'horizontal',
											'return_format' => 'value',
										),
										array(
											'key' => 'field_5aec56b3a2fbb',
											'label' => 'Background Image Align (Vertical)',
											'name' => 'img-align-y',
											'type' => 'button_group',
											'instructions' => 'Choose from center (default), top or bottom. There\'s no horizontal align as the background image is set to cover the hero.',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'choices' => array(
												'center' => '<span class="dashicons dashicons-align-center"></span> Center',
												'top' => '<span class="dashicons dashicons-arrow-up-alt2"></span> Top',
												'bottom' => '<span class="dashicons dashicons-arrow-down-alt2"></span> Bottom',
											),
											'allow_null' => 0,
											'default_value' => 'center',
											'layout' => 'horizontal',
											'return_format' => 'value',
										),
										array(
											'key' => 'field_5aec56b3a3040',
											'label' => '<span class="screen-reader-text">Opacity</span>',
											'name' => 'if-opacity',
											'type' => 'true_false',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => 'wpb-true-false',
												'id' => '',
											),
											'message' => 'Helps to add a little more separation between the background image and text.',
											'default_value' => 0,
											'ui' => 1,
											'ui_on_text' => 'Change opacity',
											'ui_off_text' => 'Change opacity',
										),
										array(
											'key' => 'field_5aec56b3a30bf',
											'label' => '<span class="screen-reader-text">Choose Opacity</span>',
											'name' => 'opacity',
											'type' => 'range',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => array(
												array(
													array(
														'field' => 'field_5aec56b3a3040',
														'operator' => '==',
														'value' => '1',
													),
												),
											),
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'default_value' => 5,
											'min' => 1,
											'max' => 9,
											'step' => '',
											'prepend' => '',
											'append' => '',
										),
										array(
											'key' => 'field_5aec56b3a313f',
											'label' => 'Opacity Colour',
											'name' => 'opac-col',
											'type' => 'color_picker',
											'instructions' => 'Choose a background colour and combine it with opacity and a gradient to create a super-cool-colour-opacity-gradient image overlay. Or just choose a colour for the opacity. (<strong>default: #ffffff</strong>).',
											'required' => 0,
											'conditional_logic' => array(
												array(
													array(
														'field' => 'field_5aec56b3a3040',
														'operator' => '==',
														'value' => '1',
													),
												),
											),
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'default_value' => '#ffffff',
										),
										array(
											'key' => 'field_5aec56b3a31c7',
											'label' => '',
											'name' => '',
											'type' => 'tab',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'placement' => 'top',
											'endpoint' => 1,
										),
										array(
											'key' => 'field_5aec56b3a3246',
											'label' => '<span class="dashicons dashicons-admin-generic"></span> More',
											'name' => '',
											'type' => 'accordion',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'open' => 0,
											'multi_expand' => 1,
											'endpoint' => 0,
										),
										array(
											'key' => 'field_5aec56b3a32bf',
											'label' => '<span class="dashicons dashicons-editor-code"></span> Custom Class',
											'name' => 'class',
											'type' => 'text',
											'instructions' => 'If you would like to add a custom class, do so here.',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'default_value' => '',
											'placeholder' => 'Enter Class (don\'t prefix with dot)',
											'prepend' => '',
											'append' => '',
											'maxlength' => '',
										),
										array(
											'key' => 'field_5aec56b3a3338',
											'label' => '<span class="dashicons dashicons-editor-code"></span> Custom ID',
											'name' => 'id',
											'type' => 'text',
											'instructions' => 'If you would like to add a custom ID, do so here.',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'default_value' => '',
											'placeholder' => 'Enter ID (don\'t prefix with hash)',
											'prepend' => '',
											'append' => '',
											'maxlength' => '',
										),
									),
									'min' => '',
									'max' => '',
								),
							),
							'button_label' => 'Add Block',
							'min' => '',
							'max' => '',
						),
					),
					'location' => array(
						array(
							array(
								'param' => 'post_type',
								'operator' => '==',
								'value' => 'page',
							),
						),
						array(
							array(
								'param' => 'post_type',
								'operator' => '==',
								'value' => 'post',
							),
						),
					),
					'menu_order' => 1,
					'position' => 'acf_after_title',
					'style' => 'seamless',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen' => '',
					'active' => 1,
					'description' => '',
				));

			endif;

		}

	}

	// initialize
	new wp_blocks();;

endif;

?>
