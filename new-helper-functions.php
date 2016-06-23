<?php 
	
	// new function for ACF to convert acf post id into term id 
	// this is used when updating db 
	// and for backwards compatability in api-value.php
	function acf_convert_post_id_to_term_id($post_id) {
		$term_id = 0;
		global $wp_taxonomies;
		foreach ($wp_taxonomies as $taxonomy => $object) {
			$taxonomy .= '_';
			if (substr($post_id, 0, strlen($taxonomy) == $taxonomy)) {
				$post_id = str_replace($taxonomy, '', $post_id);
				if (preg_match('/^([0-9]+)/', $post_id, $matches)) {
					$term_id = intval($matches[1]);
					return $term_id;
				}
			}
		}
		return $term_id;
	}
	
	// new function to extract field name from acf post id
	// this is only use during db update
	function acf_convert_post_id_to_field_name($post_id) {
		$name = '';
		global $wp_taxonomies;
		foreach ($wp_taxonomies as $taxonomy => $object) {
			$taxonomy .= '_';
			if (substr($post_id, 0, strlen($taxonomy) == $taxonomy)) {
				$post_id = str_replace($taxonomy, '', $post_id);
				$name = preg_replace('/^[0-9]+_/', '', $post_id);
			}
		}
		return $name;
	}
	
	// a function to get a term by only the id
	// this should already be added to wp since
	// splitting terms but you still need to provide
	// the taxonomy, which is a bit stupid
	function acf_get_taxonomy_term_by_term_id($term_id) {
		$taxonomy = '';
		global $wp_taxonomies;
		foreach ($wp_taxonomies as $taxonomy => $object) {
			if (get_term_by('id', $term_id, $taxonomy)) {
				return $taxonomy;
			}
		}
		return $taxonomy;
	}
	
	
?>