<?php

if ( ! class_exists( 'Splash_Fields' ) ) {
    class Splash_Fields {

        public function get_field( $key, $empty_value = null ) {
            splash_meta_box()->get_field( $key, $empty_value = null );
        }
        /*
        public function render_title( $options) {

        }
        
        public function render_field_set( $options ) {

        }
        */
        public function render( $options ) {
            ?>
            <div class="splash-field">
                <div class="splash-field__title">
                    <?php
                    $this->render_title( $options );
                    ?>
                </div>
                <div class="splash-field__field-set">
                	<?php
					$this->render_field_set( $options );
                    if (isset($options['description'])) {
                        if( $options['description'] != '' ) {
                            echo '<p class="splash-field__description">' . $options['description'] . '</p>';
                        }
                    }
					?>
				<div>
			</div>
			<?php
        }

        public function validate( $key ) {

        }

    }
}