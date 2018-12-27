<?php
/*
Plugin Name: My Wiki Posts
Description: A simple plugin for fetching wikipeadia pages' summary and feeding it to your wordpress site as post items.
Version: 1.0
Author: Levyb
Author URI: http://levybee.com
*/

define('ROOTDIR', plugin_dir_path(__FILE__));


if(file_exists( dirname( __FILE__ ) . "/_inc/functions.php" ) ){
		
	include_once( dirname( __FILE__ ) . "/_inc/functions.php" );
	
}

/**
 * Enqueue all js and  css to the mwp page
 */

function load_css_and_js() {
	
	
    global $wp_scripts;
	
	$plug_path = plugin_dir_url( __FILE__ );
	
    
	//JS  
	wp_enqueue_script('jquery-core');
	 
    // tell WordPress to load jQuery UI progressbar
    wp_enqueue_script('jquery-ui-progressbar');
	
	//AJax scripts 
	wp_register_script( 
         'mwp-ajax',
         plugin_dir_url( __FILE__ ) . 'assets/_js/mwp_ajax.js',
         array( 'jquery' )
    );
	
	wp_localize_script( 
        'mwp-ajax', 
        'mwpAjax',
        array( 
              'ajaxurl'     => admin_url( 'admin-ajax.php' ),
              'ajaxnonce'   => wp_create_nonce( 'ajax_post_validation' ) 
         ) 
    );

    wp_enqueue_script('mwp-ajax');
    
 
    // get registered script object for jquery-ui
    $ui = $wp_scripts->query('jquery-ui-core');
 
 
    //CSS
	
	if($ui){
		
		// tell WordPress to load the Smoothness theme from Google CDN
       $url = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
       wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
	}
   
	wp_enqueue_style( 'mwp-css', plugins_url('assets/_css/admin.css', __FILE__) );
}


if(isset($_GET['page']) && $_GET['page'] == 'my-wiki-posts'){
	
	add_action('init', 'load_css_and_js');
}

//menu items
add_action('admin_menu','mwp_modifymenu');
function mwp_modifymenu() {
	
	//settings menu item
	add_options_page('My Wiki Posts - Settings', //page title
	'My Wiki Posts', //menu title
	'manage_options', //capabilities
	'my-wiki-posts', //menu slug
	'mwp_wiki_settings' //function
	);
	
	
}

