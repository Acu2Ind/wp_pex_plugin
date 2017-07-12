<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Custom Pex Links Metabox For Links Post Type.
 */
class Pex_Links_Metabox {

	private $render_fields1_ind;
	
	/**
	 * List of custom fields.
	 */
	public $fields = array(
		array(
			'name'              => '_pex_links_target',
			'title'             => 'PEX',
			'description'       => '* Enter the PEX url and click Publish',
			'type'              => 'url',
			'required'          => 'required',
			'sanitize_callback' => 'esc_url_raw'
		),
		array(
			'name'        => 'productName',
			'title'       => 'Description',
			'description' => 'Describe your link',
			'type'        => 'text_ro'
		),
		
		array(
			//'name'        => '_pex_links_sku',
			'name'        => 'productID',
			'title'       => 'SKU',
			'description' => 'SKU Desc',
			'type'        => 'text_ro'
		),		
		array(
			'name'        => 'client',
			'title'       => 'Brand',
			'description' => 'Brand Desc',
			'type'        => 'text_ro'
		),
		array(
			'name'        => '_pex_links_variant',
			'title'       => 'Variant',
			'description' => 'Variant Desc',
			'type'        => 'text'
		),		
		
	);	
	
	public $admin_grid_fields = array(
		'_pex_links_target',
		'productName',
		'productID',
		'client',
		'_pex_links_variant'
	);

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {

		// Add metabox actions.
		add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

		// Add custom field values to admin grid columns.
		add_filter( 'manage_posts_columns', array( $this, 'columns_head' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'restrict_links_by_cat' ) );

		// Add custom styling.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}


	/**
	 * Add admin css file.
	 */
	public function enqueue_scripts( $hook ) {
		global $post;
		if ( $hook != 'post.php' AND $hook != 'post-new.php' AND $hook != 'pex-links_page_pex_links' ) {
			return;
		}

		//css
		wp_register_style( 'pex-links-css', PEX_LINKS_PLUGIN_URL . 'admin/css/pex-links-admin.css', false, '1.6' );
		wp_enqueue_style( 'pex-links-css' );

		//js
		wp_register_script( 'pex-links-js', PEX_LINKS_PLUGIN_URL . 'admin/js/pex-links-admin.js', array( 'jquery' ), '1.6', true );

		if ( $post ) {
			wp_localize_script(
				'pex-links-js',
				'afLinksAdmin', array(
				'linkId'    => $post->ID,
				'permalink' => get_the_permalink( $post->ID ),
				'shortcode' => 'px_link'
			) );
		}


		wp_enqueue_script( 'pex-links-js', false, array( 'jquery' ), '1.6', true );

	}

	/**
	 * Modify admin grid column headers.
	 */
	public function columns_head( $defaults ) {

		global $typenow;

		if ( $typenow == Pex_Links::$post_type ) {

			$defaults['permalink'] = __( 'Link URL', 'pex-links' );

			foreach ( $this->get_fields() as $field ) {

				if ( in_array( $field['name'], $this->admin_grid_fields ) ) {
					$defaults[ $field['name'] ] = $field['title'];
				}

			}

			/***$defaults['_pex_links_stat'] = __( 'Hits', 'pex-links' );***/
			$defaults['_pex_links_author'] = __( 'Author', 'pex-links' );

		}

		return $defaults;

	}

	/**
	 * Modify admin grid columns.
	 */
	public function columns_content( $column_name, $post_id ) {

		switch ( $column_name ) {
			case 'permalink' :
				echo esc_html( get_the_permalink( $post_id ) );
				break;
			case '_pex_links_target' :
				echo esc_html( get_post_meta( $post_id, '_pex_links_target', true ) );
				break;			
			case 'productName' :
				echo esc_html( get_post_meta( $post_id, 'productName', true ) );
				break;			
		        case 'productID' :
				echo esc_html( get_post_meta( $post_id, 'productID', true ) );			        
				break;
			case 'client' :
				echo esc_html( get_post_meta( $post_id, 'client', true ) );			        
				break;
			case '_pex_links_variant' :
				echo esc_html( get_post_meta( $post_id, '_pex_links_variant', true ) );			        
				break;
			case '_pex_links_author' :
				echo esc_html( get_the_author());			        
				break;			
		}

	}

	/**
	 * Add link category filter to admin grid.
	 */
	function restrict_links_by_cat() {

		global $typenow;
		global $wp_query;

		if ( $typenow == Pex_Links::$post_type ) {

			if ( ! empty( $wp_query->query[ Pex_Links::$taxonomy ] ) ) {
				$selected = $wp_query->query[ Pex_Links::$taxonomy ];
			} else {
				$selected = 0;
			}

			wp_dropdown_categories( array(
				'show_option_all' => __( "All Categories", 'pex-links' ),
				'taxonomy'        => Pex_Links::$taxonomy,
				'value_field'     => 'slug',
				'name'            => Pex_Links::$taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'hierarchical'    => true,
				'depth'           => 3,
				'show_count'      => true,
				'hide_empty'      => true,
			) );

		}

	}

	/**
	 * Add appropriate actions.
	 */
	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 0 );
		add_action( 'save_post', array( $this, 'save' ) );

	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {

		$post_types = array( Pex_Links::$post_type );

		if ( in_array( $post_type, $post_types ) ) {

			add_meta_box(
				'pex_links_settings',
				__( 'Settings', 'pex-links' ),
				array( $this, 'render_metabox_content' ),
				$post_type,
				'normal',
				'high'
			);

			add_meta_box(
				'pex_links_embed',
				//__( 'Link Embedding', 'pex-links' ),
				__( 'Localization', 'pex-links' ),
				array( $this, 'render_metabox_embed' ),
				$post_type,
				'normal',
				'high'
			);

			add_meta_box(
				'pex_links_sidebar',
				__( 'Information', 'pex-links' ),
				array( $this, 'render_metabox_sidebar' ),
				$post_type,
				'side',
				'default'
			);

		}

	}

	public function is_form_skip_save( $post_id ) {
		return ( ! isset( $_POST['pex_links_custom_box_nonce'] ) )
		       || ( ! wp_verify_nonce( $_POST['pex_links_custom_box_nonce'], 'pex_links_custom_box' ) )
		       || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		       || ( ! current_user_can( 'edit_post', $post_id ) );
	}

	/**
	 * Save metabox.
	 */
	public function save( $post_id ) {

		if ( $this->is_form_skip_save( $post_id ) ) {
			return $post_id;
		}

		foreach ( $this->get_fields() as $field ) {

			// Update the meta field.
			update_post_meta( $post_id, $field['name'], $this->get_sanitized_value( $field ) );

		}		
		
		foreach ( $this->get_fields1($post_id ) as $nam=>$val) {
			update_post_meta( $post_id, $nam, $this->get_sanitized_value1($nam) );
		}		
	}

	public function get_sanitized_value( $field ) {
		if ( ! isset( $_POST[ $field['name'] ] ) ) {
			return '';
		}
		$sanitize_callback = ( isset( $field['sanitize_callback'] ) ) ? $field['sanitize_callback'] : 'sanitize_text_field';

		return call_user_func( $sanitize_callback, $_POST[ $field['name'] ] );
	}
	
	public function get_sanitized_value1( $field ) {	
			
		if ( ! isset( $_POST[ $field ] ) ) {
			return '';
		}
		$sanitize_callback = ( isset( $field['sanitize_callback'] ) ) ? $field['sanitize_callback'] : 'sanitize_text_field';

		return call_user_func( $sanitize_callback, $_POST[ $field ] );
	}

	public function get_fields() {
		return apply_filters( 'px_links_get_fields', $this->fields );
	}
	
	public function get_fields1($post_id) {
		return apply_filters( 'px_links_get_fields', $this->parse_function($post_id));
	}

	/**
	 * Render metabox content.
	 */
	public function render_metabox_content( $post ) {
		global $post_type_object;
		echo '<table class="form-table">';

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'pex_links_custom_box', 'pex_links_custom_box_nonce' );
		
		$this->render_fields( $post->ID );

		/*$this->stats_field( $post->ID );*/

		echo '</table>';

	}

	public function render_fields( $id ) {
		foreach ( $this->get_fields() as $field ) {

			// Retrieve an existing value from the database.
			$value = ( isset( $field['name'] ) ) ? get_post_meta( $id, $field['name'], true ) : '';
			if ($value)
			   {
			    $dummy=1;
			   }
                        else
			   {
				$str = file_get_contents(esc_html( get_post_meta( $id, '_pex_links_target', true )."/package.json" ));				
				$json = json_decode($str, true); // decode the JSON into an associative array
				$value = $json[$field['name']];
			   }
			$this->render_field( $field, $value );

		}
	}
	
	public function render_fields1( $id ) {
		foreach ( $this->get_fields1($id) as $field=>$val) {

			// Retrieve an existing value from the database.
			//$value = ( isset( $field['val'] ) ) ? get_post_meta( $id, $field['val'], true ) : '';
			$value = ( isset( $field ) ) ? get_post_meta( $id, $field, true ) : '';
			if ($value)
			   {
			    $dumm=1;
			   }
                        else
			   {				
			$value = $val;
		        }		        
		       $this->render_fields1_ind='Y';		        
			$this->render_field( $field, $value);
			
		}
	}

	/**
	 * Render sidebar  metabox content.
	 */
	public function render_metabox_sidebar( $post ) {
		load_template( dirname( __FILE__ ) . '/partials/metabox-sidebar.php' );
	}

	public function get_embedded_metabox_fields() {
		return array(
			array(
				'thead'       => __( 'Add rel="nofollow"', 'pex-links' ),
				'class'       => 'pex_links_control',
				'data-attr'   => 'rel',
				'data-value'  => 'nofollow',
				'description' => __( 'Discourage search engines from following this link', 'pex-links' ),
				'type'        => 'embed_checkbox',
			),
			array(
				'thead'       => __( 'Add target="_blank', 'pex-links' ),
				'class'       => 'pex_links_control',
				'data-attr'   => 'target',
				'data-value'  => '_blank',
				'description' => __( 'Link will be opened in a new browser tab', 'pex-links' ),
				'type'        => 'embed_checkbox',
			),
			array(
				'thead'       => __( 'Add link title', 'pex-links' ),
				'class'       => 'pex_links_control',
				'data-attr'   => 'title',
				'description' => __( 'Title text on link hover', 'pex-links' ),
				'type'        => 'embed_text',
			),
			array(
				'thead'       => __( 'Add link class', 'pex-links' ),
				'class'       => 'pex_links_control',
				'data-attr'   => 'class',
				'description' => __( 'CSS class for custom styling', 'pex-links' ),
				'type'        => 'embed_text',
			),
			array(
				'thead'       => __( 'Add link anchor', 'pex-links' ),
				'class'       => 'pex_links_control',
				'data-attr'   => 'anchor',
				'description' => __( 'Clickable link text', 'pex-links' ),
				'type'        => 'embed_text',
			),

		);
	}
	
	
	public function parse_function($post_id) {
	
	$str2 = file_get_contents(esc_html(get_post_meta( $post_id, '_pex_links_target', true )."/package.json" ));
	$json2 = json_decode($str2, true); // decode the JSON into an associative array	
	$local= $json2 ['localization'];	
	$str3 = file_get_contents(esc_html(get_post_meta( $post_id, '_pex_links_target', true )."/".$local ));	
	$str3 = preg_replace("/parse\(/", "", $str3);
        $str3 = preg_replace("/\)\;/", "", $str3);        
	$json3 = json_decode($str3, true); // decode the JSON into an associative array	
	return $json3;	
	}
	
	/**
	 * Render embed metabox content.
	 */
	public function render_metabox_embed( $post ) {
		global $post_type_object;

		
		$sample_permalink_html = $post_type_object->public ? get_sample_permalink_html( $post->ID ) : '';

		if ( $post_type_object->public
		     && ! ( 'pending' == get_post_status( $post ) && ! current_user_can( $post_type_object->cap->publish_posts ) )
		) {
			$has_sample_permalink = $sample_permalink_html && 'auto-draft' != $post->post_status;
			if ( $has_sample_permalink ) {
				echo '<table class="form-table hide-if-no-js"';
				echo '<table class="form-table"';
				$this->render_fields1( $post->ID );
				echo '</table>';
				load_template( dirname( __FILE__ ) . '/partials/metabox-embed.php' );
			} else {
				echo '<p>' . __( 'Before you can view or edit localization strings you need to publish the PEX.' ) . '</p>';
			}
		} 
	}

	/**
	 * Generate settings field html.
	 */
	public function render_field( $field, $value ) {	 

		$func_name = 'render_' . $field['type'] . '_field';		
		
		
		if ($this->render_fields1_ind == 'Y') {call_user_func_array( array( $this, 'render_text_field1' ), array( 'field' => $field, 'value' => $value ) );}else {

		if ( method_exists( __CLASS__, $func_name ) ) {

			call_user_func_array( array( $this, $func_name ), array( 'field' => $field, 'value' => $value ) );

		} else {

			call_user_func_array( array( $this, 'render_text_field' ), array( 'field' => $field, 'value' => $value ) );

		}
		}

	}

	/**
	 * Generate text input field.
	 */
	public function render_text_field( $field, $value ) {
	
		$name  = esc_attr( $field['name'] );
		$title = esc_attr( $field['title'] );
		$desc  = esc_html( $field['description'] );
		$type  = esc_attr( $field['type'] );		
		?>
		<tr>
			<th>
				<label for="<?php echo $name ?>" class="<?php echo $name ?>_label"><?php echo $title?></label>
				<!--<label for="<?php echo $field?>" class="<?php echo $field?>_label"><?php echo $field?></label>-->
			</th>
			<td>
				<input
					type="<?php echo $type ?>"
					id="<?php echo $name ?>"
					name="<?php echo $name ?>"
					class="<?php echo $name ?>_field"
					<?php if ( ! empty( $field['required'] ) )
						echo $field['required'] ?>
					value="<?php echo esc_attr__( $value ) ?>"										
				>				
				<p class="description"><?php echo $desc ?></p>				
			</td>
		</tr>
		<?php

	}
	
	public function render_text_ro_field( $field, $value ) {
	
		$name  = esc_attr( $field['name'] );
		$title = esc_attr( $field['title'] );
		$desc  = esc_html( $field['description'] );
		$type  = esc_attr( $field['type'] );		
		?>
		<tr>
			<th>
				<label for="<?php echo $name ?>" class="<?php echo $name ?>_label"><?php echo $title?></label>
				<!--<label for="<?php echo $field?>" class="<?php echo $field?>_label"><?php echo $field?></label>-->
			</th>
			<td>
				<input
					type="<?php echo $type ?>"
					id="<?php echo $name ?>"
					name="<?php echo $name ?>"
					class="<?php echo $name ?>_field"
					<?php if ( ! empty( $field['required'] ) )
						echo $field['required'] ?>
					value="<?php echo esc_attr__( $value ) ?>"
					size="100"
					readonly
										
				>				
				<p class="description"><?php echo $desc?></p>				
			</td>
		</tr>
		<?php

	}
	
	/**
	 * Generate text input field.
	 */
	public function render_text_field1( $field, $value ) {
			
		$name  = esc_attr( $field );
		$title = esc_attr( $field['title'] );
		$desc  = esc_html( $field['description'] );
		$type  = esc_attr( $field['type'] );		
		?>
		<tr>
			<th>
				<!--<label for="<?php echo $name ?>" class="<?php echo $name ?>_label"><?php echo $title?></label>-->
				<label for="<?php echo $field?>" class="<?php echo $field?>_label"><?php echo $field?></label>
			</th>
			<td>
				<input
					type="<?php echo $type ?>"
					id="<?php echo $name ?>"
					name="<?php echo $name ?>"
					size="75"
					class="<?php echo $name ?>_field"
					<?php if ( ! empty( $field['required'] ) )
						echo $field['required'] ?>
					value="<?php echo esc_attr__( $value ) ?>"					
				>				
				<!--<p class="description"><?php echo $desc ?></p>-->	
						
			</td>
		</tr>
		<?php

	}


	/**
	 * Generate checkbox field.
	 */
	public function render_checkbox_field( $field, $value ) {

		$name  = esc_attr( $field['name'] );
		$title = esc_attr( $field['title'] );
		$desc  = esc_html( $field['description'] );
		$type  = esc_attr( $field['type'] );

		if ( ! empty( Pex_Links::$settings[ $field['global_name'] ] ) ) {
			$default_val = Pex_Links::$settings[ $field['global_name'] ];
		} else {
			$default_val = 0;
		}

		$checked_value = ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) ? $value : $default_val;
		?>
		<tr>
			<th>
				<label for="<?php echo $name ?>" class="<?php echo $name ?>_label"><?php echo $title ?></label>
			</th>
			<td>
				<input
					type="<?php echo $type ?>"
					id="<?php echo $name ?>"
					name="<?php echo $name ?>"
					class="<?php echo $name ?>_field"
					value="1"
					<?php checked( $checked_value, 1 ) ?>
				>
				<label for="<?php echo $name ?>">
					<?php echo $desc ?>
				</label>
			</td>
		</tr>
		<?php

	}

	public function render_embed_checkbox_field( $field, $value ) {
		?>
		<tr>
			<th>
				<?php echo $field['thead'] ?>
			</th>
			<td>
				<label>
					<input type="<?php echo esc_attr( trim( $field['type'], 'embed_' ) ) ?>"
					       class="<?php echo esc_attr( $field['class'] ) ?>"
					       data-attr="<?php echo esc_attr( $field['data-attr'] ) ?>"
					       data-value="<?php echo esc_attr( $field['data-value'] ) ?>"
					>
					<?php echo esc_html( $field['description'] ) ?>
				</label>
			</td>
		</tr>
		<?php
	}

	public function render_embed_text_field( $field, $value ) {
		?>
		<tr>
			<th>
				<?php echo $field['thead'] ?>
			</th>
			<td>
				<label>
					<input type="<?php echo esc_attr( trim( $field['type'], 'embed_' ) ) ?>"
					       class="<?php echo esc_attr( $field['class'] ) ?>"
					       data-attr="<?php echo esc_attr( $field['data-attr'] ) ?>"
					>
					<p class="description"><?php echo esc_html( $field['description'] ) ?></p>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * Generate radio button fields.
	 */
	public function render_radio_field( $field, $value ) {

		$title = esc_attr( $field['title'] );
		$desc  = esc_html( $field['description'] );
		$type  = esc_attr( $field['type'] );

		$values = $field['values'];
		reset( $values );
		$default_val = key( $values );

		if ( ! empty( Pex_Links::$settings[ $field['global_name'] ] ) ) {
			$default_val = Pex_Links::$settings[ $field['global_name'] ];
		}

		$checked_value = empty( $value ) ? $default_val : $value;
		?>
		<tr>
			<th><?php echo $title ?></th>
			<td>
				<?php foreach ( $values as $key => $value ) { ?>
					<input
						type="<?php echo $type ?>"
						id="<?php echo esc_attr( $field['name'] . '_' . $key ) ?>"
						name="<?php echo esc_attr( $field['name'] ) ?>"
						value="<?php echo esc_attr( $key ) ?>"
						<?php checked( $checked_value, $key ) ?>
					>
					<label for="<?php echo esc_attr( $field['name'] . '_' . $key ) ?>">
						<?php echo esc_html( $value ) ?>
					</label>
					<br>
				<?php } ?>
				<p class="description"><?php echo $desc ?></p>
			</td>
		</tr>
		<?php

	}
	
	/**
	 * Generate fields for permalink displaying.
	 */
	public function link_field( $post_id ) {

		?>
		<tr>
			<th><?php _e( 'Your link', 'pex-links' ) ?></th>
			<td>
				<span class="pex_links_copy_link"><?php the_permalink( $post_id ) ?></span>
                <span class="pex_links_copy_button">
                    <button type="button"
                            class="button button-small hide-if-no-js"><?php _e( 'Copy', 'pex-links' ) ?></button>
                </span>
				<p class="description"><?php _e( 'To change this link you should edit Permalink at the top of screen', 'pex-links' ) ?></p>
			</td>
		</tr>
		<?php

	}

}

/**
 * Calls the class on the post edit screen.
 */
if ( is_admin() ) {

	new Pex_Links_Metabox();

}