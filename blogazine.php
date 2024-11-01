<?php
/*
Plugin Name: Style Replacement
Plugin URI: http://www.wordpress.org/extend/plugins/style-replacement/
Description: This plugin works to replacing theme with a dropdown selection. Plugin Style Replacement Created By <a href="http://twitter.com/amdhas">Amdhas</a>.
Version: 1.1
Author: Amdhas
Author URI: http://twitter.com/amdhas
*/
if(!function_exists('get_post_templates')) {
function get_post_templates() {
$themes = get_themes();
$theme = get_current_theme();
$templates = $themes[$theme]['Template Files'];
$post_templates = array();

$base = array(trailingslashit(get_template_directory()), trailingslashit(get_stylesheet_directory()));
foreach ((array)$templates as $template) {
$template = WP_CONTENT_DIR . str_replace(WP_CONTENT_DIR, '', $template); 
$basename = str_replace($base, '', $template);

if (false !== strpos($basename, '/'))
continue;
$template_data = implode('', file( $template ));
$name = '';
if (preg_match( '|Style : (.*)$|mi', $template_data, $name))
$name = _cleanup_header_comment($name[1]);
if (!empty($name)) {
if(basename($template) != basename(__FILE__))
$post_templates[trim($name)] = $basename;}}
return $post_templates;}}

if(!function_exists('post_templates_dropdown')) {
function post_templates_dropdown() {
global $post;
$post_templates = get_post_templates();
foreach ($post_templates as $template_name => $template_file) { 
if ($template_file == get_post_meta($post->ID, '_wp_post_template', true)) { $selected = ' selected="selected"'; } else { $selected = ''; }
$opt = '<option value="' . $template_file . '"' . $selected . '>' . $template_name . '</option>';
echo $opt;}}}

add_filter('single_template', 'get_post_template');
if(!function_exists('get_post_template')) {
function get_post_template($template) {
global $post;
$custom_field = get_post_meta($post->ID, '_wp_post_template', true);
if(!empty($custom_field) && file_exists(TEMPLATEPATH . "/{$custom_field}")) { 
$template = TEMPLATEPATH . "/{$custom_field}"; }
return $template;}}

add_action('admin_menu', 'pt_add_custom_box');
function pt_add_custom_box() {
if(get_post_templates() && function_exists( 'add_meta_box' )) {
add_meta_box( 'pt_post_templates', __( 'Choose a Single Post Style', 'pt' ), 
'pt_inner_custom_box', 'post', 'normal', 'high' );}}

function pt_inner_custom_box() {
global $post;
echo '<input type="hidden" name="pt_noncename" id="pt_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
echo '<label class="hidden" for="post_template">' . __("Post Template", 'pt' ) . '</label><br />';
echo '<select name="_wp_post_template" id="post_template" class="dropdown">';
echo '<option value="">Style Default</option>';
post_templates_dropdown();
echo '</select><br /><br />';
echo '<p>' . __("You can choose the style you've created. When you do not make it, <a href=\"http://www.nurulimam.info/plugin-style-replacement/\">Please see here</a>", 'pt' ) . '</p><br />';}

add_action('save_post', 'pt_save_postdata', 1, 2);
function pt_save_postdata($post_id, $post) {

if ( !wp_verify_nonce( $_POST['pt_noncename'], plugin_basename(__FILE__) )) {
return $post->ID;}

if ( 'page' == $_POST['post_type'] ) {
if ( !current_user_can( 'edit_page', $post->ID ))
return $post->ID;
} else {
if ( !current_user_can( 'edit_post', $post->ID ))
return $post->ID;
}

$mydata['_wp_post_template'] = $_POST['_wp_post_template'];
foreach ($mydata as $key => $value) { 
if( $post->post_type == 'revision' ) return;
$value = implode(',', (array)$value);
if(get_post_meta($post->ID, $key, FALSE)) { 
update_post_meta($post->ID, $key, $value);
} else { 
add_post_meta($post->ID, $key, $value);}
if(!$value) delete_post_meta($post->ID, $key);}}
?>