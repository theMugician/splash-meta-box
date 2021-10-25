<?php
if ( ! class_exists( 'Splash_Fields_File_Upload' ) ) {
    class Splash_Fields_File_Upload extends Splash_Fields {

        public function register( $meta_box_id ) {
        }

        public function render_title($options) {
            ?>
            <label class="splash-field__label" for="<?php echo $id; ?>"><?php echo $title; ?></label>
            <?php
        }
        
        public function render_field_set($options) {
            ?>
            <input
                class="splash-field__input"
                type="file"
                name="<?php echo $options['name']; ?>" 
                id="<?php echo $id; ?>" 
                value="" 
            />
            <?php 

        }

        public function validate( $key ) {

        }


        public function save_fileupload( $post_id, $key, $value ) {
            if ( ! empty( $_FILES[$key]['name'] ) ) {
                $supported_types = array( 'audio/mpeg' );
                $arr_file_type = wp_check_filetype( basename( $_FILES[$key]['name'] ) );
                $uploaded_type = $arr_file_type['type'];
        
                if ( in_array( $uploaded_type, $supported_types ) ) {
                    $upload = wp_upload_bits($_FILES[$key]['name'], null, file_get_contents($_FILES[$key]['tmp_name']));
                    if ( isset( $upload['error'] ) && $upload['error'] != 0 ) {
                        wp_die( 'There was an error uploading your file. The error is: ' . $upload['error'] );
                    } else {
                        add_post_meta( $post_id, $key, $upload );
                        update_post_meta( $post_id, $key, $upload );
                    }
                }
                else {
                    wp_die( "The file type that you've uploaded is not a music file." );
                }
            }
        }
        
    }
}