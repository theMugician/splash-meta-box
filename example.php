<?php

require plugin_dir_path(__FILE__) . '../includes/class-splash-meta-box.php';

/**
 * Register meta box for the Splash Audio Player Post Type
 *
 * @var string   $id
 * @var string   $title
 * @var string   $context
 * @var string   $priority
 * @var array    $screens.   
 */  

$id = 'splash_audio_player_settings';
$title = 'Audio Player Settings';
$context = 'advanced';
$priority = 'default';
$screens = array();

$splash_audio_player_meta_box = new Splash_Meta_Box;
$splash_audio_player_meta_box->register( $id, $title, $context, $priority, $screens );

/**
 * Add text field to meta box
 * @var  string $id
 * @var  string $title
 * @var  array  $options *Optional
 * 
 */
$text_array = array(
    'id' => 'splash_audio_player_text',
    'title' => 'Audio Player Text',
); 

$splash_audio_player_meta_box->add_field( $text_array );