<?php
require plugin_dir_path(__FILE__) . 'class-splash-fields.php';

if ( ! class_exists( 'Splash_Fields_Text' ) ) {
    class Splash_Fields_Text extends Splash_Fields {

        public function render_title( $options ) {
            if (isset($options['title'])) {
            ?>
            <label class="splash-field__label" for="<?php echo $options['id']; ?>"><?php echo $options['title']; ?></label>
            <?php
            }
        }
        
        public function render_field_set( $options ) {
            ?>
            <input
                class="splash-field__input"
                type="text"
                <?php 
                if (isset($options['id'])) {
                ?>
                name="<?php echo $options['id']; ?>" 
                id="<?php echo $options['id']; ?>" 
                <?php
                }
                ?>
                value="<?php if (isset($options['default'])) echo $options['default']; ?>" 
            />
            <?php 

        }
        
        public function validate( $key ) {
            $value = $this->get_field( $key );
            // $get_settings = get_post_meta( $post->ID, 'extra_settings', true);
            // $this->value = '';
            // var_dump($_POST[$key]);
            // die();
            
            if ( isset( $_POST[$key] ) ) {
                // echo $_POST[$key];
                
                $value = sanitize_text_field( $_POST[$key] );
            }
            // var_dump($value);
            //die();
            return $value;      
        }

        public function save_checkbox( $post_id, $key, $value ) {
            $allowed_values = array( 0, 1 ); 
            if ( ! isset( $_POST[$key] ) ) {
                $value = 0;
            }
            $value = intval( $value );
            if ( in_array( $value, $allowed_values, false ) ) {
                update_post_meta( $post_id, $key, $value );
            }
        }

        public function save_radio( $post_id, $key, $value ) {
            if ( isset( $_POST[$key] ) ) {
                $value = sanitize_text_field( $value );
            }
            update_post_meta( $post_id, $key, $value );
        }

        
    }
}