<?php
/*
 * Plugin Name: Third Wunder Documents Plugin
 * Version: 1.0
 * Plugin URI: http://www.thirdwunder.com/
 * Description: Third Wunder Documents CPT plugin
 * Author: Mohamed Hamad
 * Author URI: http://www.thirdwunder.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: tw-documents-plugin
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Mohamed Hamad
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-tw-documents-plugin.php' );
require_once( 'includes/class-tw-documents-plugin-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-tw-documents-plugin-admin-api.php' );
require_once( 'includes/lib/class-tw-documents-plugin-post-type.php' );
require_once( 'includes/lib/class-tw-documents-plugin-taxonomy.php' );

if(!class_exists('AT_Meta_Box')){
  require_once("includes/My-Meta-Box/meta-box-class/my-meta-box-class.php");
}

/**
 * Returns the main instance of TW_Documents_Plugin to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object TW_Documents_Plugin
 */
function TW_Documents_Plugin () {
	$instance = TW_Documents_Plugin::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = TW_Documents_Plugin_Settings::instance( $instance );
	}

	return $instance;
}

TW_Documents_Plugin();
$prefix = 'tw_';

$document_slug = get_option('wpt_tw_document_slug') ? get_option('wpt_tw_document_slug') : "document";

$document_search  = get_option('wpt_tw_document_search') ? true : false;
$document_archive = get_option('wpt_tw_document_archive') ? true : false;

$document_category = get_option('wpt_tw_document_category') ? get_option('wpt_tw_document_category') : false;
$document_tag      = get_option('wpt_tw_document_tag')      ? get_option('wpt_tw_document_tag') : false;

TW_Documents_Plugin()->register_post_type(
                        'tw_document',
                        __( 'Documents',     'tw-documents-plugin' ),
                        __( 'Document',      'tw-documents-plugin' ),
                        __( 'Documents CPT', 'tw-documents-plugin'),
                        array(
                          'menu_icon'=>plugins_url( 'assets/img/cpt-icon-document.png', __FILE__ ),
                          'rewrite' => array('slug' => $document_slug),
                          'exclude_from_search' => $document_search,
                          'has_archive'     => $document_archive,
                          'supports' => array( 'title', 'excerpt' ),
                        )
                    );

if($document_category){
  TW_Documents_Plugin()->register_taxonomy( 'tw_document_category', __( 'Document Categories', 'tw-documents-plugin' ), __( 'Document Category', 'tw' ), 'tw_document', array('hierarchical'=>true) );
}

if($document_tag){
 TW_Documents_Plugin()->register_taxonomy( 'tw_document_tag', __( 'Document Tags', 'tw-documents-plugin' ), __( 'Document Tag', 'tw-documents-plugin' ), 'tw_document', array('hierarchical'=>false) );
}

if (is_admin()){
  $document_config = array(
    'id'             => 'tw_document_metabox',
    'title'          => 'Document Upload',
    'pages'          => array('tw_document'),
    'context'        => 'normal',
    'priority'       => 'high',
    'fields'         => array(),
    'local_images'   => true,
    'use_with_theme' => false
  );
  $document_meta =  new AT_Meta_Box($document_config);
  $document_meta->addFile('tw_document_file',array('name'=> 'File Upload'));
  $document_meta->Finish();
}



/******************************
*********** Filters ***********
******************************/
add_filter( 'template_include', 'tw_document_plugin_template_chooser');
function tw_document_plugin_template_chooser( $template ) {
  // Post ID
  $post_id = get_the_ID();

  $document_page = get_option('wpt_tw_document_page') ? get_option('wpt_tw_document_page') : false;
  $is_document_page = is_page($document_page);

  // For Assigned Document Page
  if($document_page && $is_document_page ){
    return tw_document_plugin_get_template_hierarchy( 'template-document' );
  }

  // For all other CPT
  if ( get_post_type( $post_id ) != 'tw_document' ) {
      return $template;
  }

  // Else use custom template
  if ( is_single() ) {
    return tw_document_plugin_get_template_hierarchy( 'single-tw_document' );
  }elseif(is_archive()){
    return tw_document_plugin_get_template_hierarchy( 'archive-tw_document' );
  }
}

function tw_document_plugin_get_template_hierarchy( $template ) {

    // Get the template slug
    $template_slug = rtrim( $template, '.php' );
    $template = $template_slug . '.php';

    // Check if a custom template exists in the theme folder, if not, load the plugin template file
    if ( $theme_file = locate_template( array($template, 'plugin_template/'.$template ) )  ) {
        $file = $theme_file;
    }else {
      $file = TW_Documents_Plugin()->dir.'/templates/'.$template;
    }

    return apply_filters( 'rc_repl_template_' . $template, $file );
}


function tw_document_plugin_archive_query( $query ) {
  if ( is_archive('tw_document') && is_main_query() ) {
    set_query_var( 'orderby', 'title' );
    set_query_var( 'order', 'ASC' ); //where search_longitude is a post meta field
  }
}
add_action( "pre_get_posts", "tw_document_plugin_archive_query" );

function tw_document_plugin_archive_title($title){
  if(is_post_type_archive('tw_document')){
    $title = __('Documents','tw-document-plugin');
  }elseif(is_tax('tw_document_topic')){
    $title = sprintf( __( '%1$s: %2$s' ), __('Topic','tw-document-plugin'), single_term_title( '', false ) );
  }
  return $title;
}
add_filter('get_the_archive_title', 'tw_document_plugin_archive_title');