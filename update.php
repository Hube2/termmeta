<?php 
	
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;


/*
		This is a quick and dirty update to move all term meta from the options
		table to the termmeta table. It does not use prepare for queries, I'm not
		sure it can since the update and delete queries are preforming all of the
		updates and deletes in single SQL statements.... however, since none of
		the values used are supplied by the user and are alreeady in the DB
		the queries should be completely safe.
		
		THIS HAS NOT BEEN TESTED
*/


// global
global $wpdb;

$query = 'SELECT option_id, option_name, option_value 
					FROM '.$wpdb->options.' 
					WHERE option_value LIKE "field\_%"';
$results = $wpdb->get_results($query);
if (!count($results)) {
	return;
}

$delete = array();
$insert = array();
$names = array();
$name_to_key = array();
foreach ($results as $result) {
	$name = substr($result['option_name'], 1);
	$term_id = acf_convert_post_id_to_term_id($name);
	if (!$term_id) {
		continue;
	}
	$delete[] = $result['option_id'];
	if (!in_array($name, $names)) {
		$names[] = $name;
	}
	$name_to_key[$name] = $result['option_value'];
} // end foreach result

if (!count($names)) {
	return;
}

$names = $wpdb->_escape($names);
$query = 'SELECT option_id, option_name, options_value 
					FROM '.$wpdb->options.' 
					WHERE option_name IN ("'.implode('","', $names).'")';

$results = $wpdb->get_results($query);
if (!count($results)) {
	return;
}
foreach ($results as $result) {
	$term_id = acf_convert_post_id_to_term_id($result['option_name']);
	$meta_key = acf_convert_post_id_to_field_name($result['option_name']);
	$meta_key_acf = $wpdb->_escape('_'.$meta_key);
	$meta_key = $wpdb->_escape($meta_key);
	if ($term_id && $meta_key) {
		$delete[] = $result['option_id'];
		$meta_value = $wpdb->_escape($result['option_value']);
		$key_value = $wpdb->_escape($name_to_key[$result['option_name']]);
		$insert[] = '("'.$term_id.'", "'.$meta_key.'", "'.$meta_value.'")';
		$insert[] = '("'.$term_id.'", "'.$meta_key_acf.'", "'.$key_value.'")';
	}
}
if (count($delete) && !apply_filters('acf/keep_taxonomy_option', false)) {
	// filter acf/keep_taxonomy_option
	// a filter for people that have used 
	// get_option instead of get_field
	// when getting acf fields for terms
	// obviousely the filter would need to be put in place
	// before someone updated ACF to the version that incorporates
	// this update
	$query = 'DELETE FROM '.$wpdb->options.' WHERE option_id IN ("'.implode('","', $delete).'")';
	$wpdb->query($query);
}
if (count($insert)) {
	$query = 'INSERT INTO '.$wpdb->termmeta.' (term_id, meta_key, meta_value) VALUES '.implode(',', $insert);
	$wpdb->query($query);
}

	
?>