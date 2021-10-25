<?php

/**
 * Splash Meta Box Utility Class
 * A WordPress meta box that appears in the editor.
 */
if ( ! class_exists( 'Splash_Meta_Box' ) ) {
    class Splash_Meta_Box
    {
        /** 
         * @var string 
         * The plugin version number. 
         */
		var $version = '0.1';
                

        /**
         * ID of the settings.
         * @var string
         */
        public $settings_id = '';

        /**
         * Settings from database including all the added fields.
         * @var array
         */
        protected $settings = array();

        /**
         * Post ID of the current post.
         * @var string
         */
        protected $post_id;

        /**
         * Screen context where the meta box should display.
         *
         * @var string
         */
        protected $context;

        /**
         * The ID of the meta box.
         *
         * @var string
         */
        protected $id;

        /**
         * The display priority of the meta box.
         *
         * @var string
         */
        protected $priority;

        /**
         * Screens where this meta box will appear.
         *
         * @var string[]
         */
        protected $screens;

        /**
         * The title of the meta box.
         *
         * @var string
         */
        protected $title;

        /**
         * Screens where this meta box will appear.
         *
         * @var string[]
         */
        protected $fields = array();

        /**
         * Text field object.
         *
         * @var object
         */
        protected $text_field;        

        /**
         * Constructor.
         * Include all relevant scripts and custom fields.
         * 
         */
        public function __construct()
        {
            // Add admin styles and scripts
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

            // Include field text object
            require plugin_dir_path(__FILE__) . 'includes/class-splash-fields-text.php';
            $this->text_field = new Splash_Fields_Text;

        }


        public function enqueue_admin_scripts() {
            wp_enqueue_style( 'splash-fields-css', plugins_url( 'admin/css/splash-fields.css', __FILE__ ), null, '');
        }
        
        /**
         * Render the content of the meta box using a PHP template.
         * Rendering fields 
         * @return void
         */
        public function render( $post ) {
            $this->post_id = $post->ID;
            $this->init_settings(); 
            wp_nonce_field( 'metabox_' . $this->id, 'metabox_' . $this->id . '_nonce' );
            if( empty( $this->fields ) || null === $this->fields  ) {
                echo '<p>' . __( 'There are no settings on this page.', 'textdomain' ) . '</p>';
                return;
            } else {
                foreach ( $this->fields as $field ) {
                    if (isset($field['type'])) {
                        $type = $field['type'];
                    }
                    $render_type = 'render_' . $type;
                    call_user_func(array($this, $render_type), $field );
                }
            }
        }
        
        /**
         * Add meta box to a post.
         *
         */
        public function register( $id, $title, $context = 'advanced', $priority = 'default', $screens = array() )
        {
            if (is_string($screens)) {
                $screens = (array) $screens;
            }

            $this->id = $id;
            $this->title = $title;
            $this->context = $context;
            $this->priority = $priority;
            $this->screens = $screens;
            $this->settings_id = $this->id; 
            // $this->fields = array();

            add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
            add_action( 'save_post', array( $this, 'save_meta_settings' ) );
        }

        public function add_meta_box() {
            add_meta_box($this->id, $this->title, array( $this, 'render' ), $this->screens );
        }

        /**
         * Add add field to the meta box.
         *
         */        
        public function add_field( $array ) {
            $allowed_field_types = array(
                'text',
                'textarea',
                'wpeditor',
                'select',
                'radio',
                'checkbox' );
    
            // If a type is set that is now allowed, don't add the field
            if( isset( $array['type'] ) && $array['type'] != '' && ! in_array( $array['type'], $allowed_field_types ) ){
                return;
            }
    
            $defaults = array(
                'id' => '',
                'title' => '',
                'default' => '',
                'placeholder' => '',
                'type' => 'text',
                'options' => array(),
                'description' => '',
            );
    
            $array = array_merge( $defaults, $array );
    
            if( $array['id'] == '' ) {
                return;
            }

            foreach ( $this->fields as $field ) {
                if( isset( $this->fields[ $array['id'] ] ) ) {
                    trigger_error( 'There is alreay a field with name ' . $array['id'] );
                    return;
                }
            }
            
            $this->fields[ $array['id'] ] = $array;

        }

        /**
         * Get the settings from the database.
         * @return void 
         */
        public function init_settings() {
            $post_id = $this->post_id;
            $this->settings = get_post_meta( $post_id, $this->settings_id, true );
        
            foreach ( $this->fields as $field ) {
                if( isset( $this->settings[ $field['id'] ] ) ) {
                    $this->fields[ $field['id'] ]['default'] = $this->settings[ $field['id'] ];
                }
            }
        }

        /**
         * 
         * Save settings from POST
         * @param WP_Post $post_id
         */
        public function save_meta_settings( $post_id ) {
        
            // Check if our nonce is set.
            if ( ! isset( $_POST['metabox_' . $this->id  . '_nonce'] ) ) {

                return $post_id;
            }
            
            $nonce = $_POST['metabox_' . $this->id  . '_nonce'];
            
            // Verify that the nonce is valid.
            if ( ! wp_verify_nonce( $nonce, 'metabox_' . $this->id  ) ) {
                echo('passed validation');
            }
            
            /*
            * If this is an autosave, our form has not been submitted,
            * so we don't want to do anything.
            */
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return $post_id;
            }
            
            // Check the user's permissions.
            if ( 'page' == $_POST['post_type'] ) {
                if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
                }
            } else {
                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
                }
            }
            
            foreach ( $this->fields as $field ) {
                $key = $field['id'];
                $type = $field['type'];
                $this->settings[ $key ] = $this->{ 'validate_' . $type }( $key );
                
            }
            // die();
            update_post_meta( $post_id, $this->settings_id, $this->settings );	

        }

        /**
         * Gets a field from the settings API, using defaults if necessary to prevent undefined notices.
         *
         * @param  string $key
         * @param  mixed  $empty_value
         * @return mixed  The value specified for the field or a default value for the field.
         */
        public function get_field( $key, $empty_value = null ) {
            if ( empty( $this->settings ) ) {
                $this->init_settings();
            }
            // Get field default if unset.
            if ( ! isset( $this->settings[ $key ] ) ) {
                $form_fields = $this->fields;
                foreach ( $form_fields as $field ) {
                    if( isset( $form_fields[ $key ] ) ) {
                        $this->settings[ $key ] = isset( $form_fields[ $key ]['default'] ) ? $form_fields[ $key ]['default'] : '';
                    }
                }
                
            }
            if ( ! is_null( $empty_value ) && empty( $this->settings[ $key ] ) && '' === $this->settings[ $key ] ) {
                $this->settings[ $key ] = $empty_value;
            }
            return $this->settings[ $key ];
        }

        // Render text field
        public function render_text( $array ) {
            $this->text_field->render( $array );
        }

        // Validate text field
        public function validate_text( $key ) {
            return $this->text_field->validate( $key );
        }        
    }

	/*
	* splash_meta_box
	*
	* The main function responsible for returning the one true splash_meta_box Instance to functions everywhere.
	* Use this function like you would a global variable, except without needing to declare the global.
	*
	* Example: <?php $splash_meta_box = splash_meta_box(); ?>
	*
	* @date    2021/10/24
	* @since   0.1.0
	*
	* @param   void
	* @return  Splash_Meta_Box_1
	*/    
	function splash_meta_box() {
		global $splash_meta_box;

		// Instantiate only once.
		if ( ! isset( $splash_meta_box ) ) {
			$splash_meta_box = new Splash_Meta_Box_1();
			// $splash_meta_box->initialize();
		}
		return $splash_meta_box;
	}

	// Instantiate.
	splash_meta_box();   

    // class_exists check 
}