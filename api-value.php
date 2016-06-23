<?php

/*
*  acf_get_metadata
*
*  This function will get a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$hidden (boolean)
*  @return	$return (mixed)
*/

function acf_get_metadata( $post_id = 0, $name = '', $hidden = false ) {
	
	// vars
	$value = null;
	
	
	// bail early if no $post_id (acf_form - new_post)
	if( !$post_id ) return $value;
	
	
	// add prefix for hidden meta
	if( $hidden ) {
		
		$name = '_' . $name;
		
	}
	
	
	// post
	if( is_numeric($post_id) ) {
		
		$meta = get_metadata( 'post', $post_id, $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
	
	// user
	} elseif( substr($post_id, 0, 5) == 'user_' ) {
		
		$user_id = (int) substr($post_id, 5);
		
		$meta = get_metadata( 'user', $user_id, $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
	
	// comment
	} elseif( substr($post_id, 0, 8) == 'comment_' ) {
		
		$comment_id = (int) substr($post_id, 8);
		
		$meta = get_metadata( 'comment', $comment_id, $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
	 	
	} elseif (substr($post_id, 0, 5) == 'term_') {
		
		// this is for future use when the rest of acf is converted to using 
		// "term_" as a prefix for all term "post ID" values
		
		$term_id = $comment_id = (int) substr($post_id, 5);
		
		$meta = get_metadata( 'term', $term_id, $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
		
	} elseif (($term_id = acf_convert_post_id_to_term_id($post_id)) != 0) {
		
		// this is for backward compatability for "{$taxonomy}_{$term_id}"
		// this will be used until acf is converted to using the "term_"
		// prefix and so that the change will not break themes using
		// the old post_id method
		// see new-helper-functions.php for more info
		
		$meta = get_metadata( 'term', $term_id, $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
		
	} else {
		
		// modify prefix for hidden meta
		if( $hidden ) {
			
			$post_id = '_' . $post_id;
			$name = substr($name, 1);
			
		}
		
		$value = get_option( $post_id . '_' . $name, null );
		
	}
		
	
	// return
	return $value;
	
}


/*
*  acf_update_metadata
*
*  This function will update a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$value (mixed)
*  @param	$hidden (boolean)
*  @return	$return (boolean)
*/

function acf_update_metadata( $post_id = 0, $name = '', $value = '', $hidden = false ) {
	
	// vars
	$return = false;
	
	
	// add prefix for hidden meta
	if( $hidden ) {
		
		$name = '_' . $name;
		
	}
	
	
	// postmeta
	if( is_numeric($post_id) ) {
		
		$return = update_metadata('post', $post_id, $name, $value );
	
	// usermeta
	} elseif( substr($post_id, 0, 5) == 'user_' ) {
		
		$user_id = (int) substr($post_id, 5);
		
		$return = update_metadata('user', $user_id, $name, $value);
		
	// commentmeta
	} elseif( substr($post_id, 0, 8) == 'comment_' ) {
		
		$comment_id = (int) substr($post_id, 8);
		
		$return = update_metadata('comment', $comment_id, $name, $value);
	
	// options	
	} elseif (substr($post_id, 0, 5) == 'term_') {
		
		// this is for future use when the rest of acf is converted to using 
		// "term_" as a prefix for all term "post ID" values
		
		$term_id = $comment_id = (int) substr($post_id, 5);
		
		if (apply_filters('acf/keep_taxonomy_option', false)) {
			// a filter for people that have used 
			// get_option instead of get_field
			// when getting acf fields for terms
			// it causes values to be saved in options as well as term meta
			$taxonomy = acf_get_taxonomy_term_by_term_id($term_id);
			acf_update_option($taxonomy.'_'.$term_id, $value);
		}
		
		$return = update_metadata('term', $meta_id, $name, $value);
		
	} elseif (($term_id = acf_convert_post_id_to_term_id($post_id)) != 0) {
		
		// this is for backward compatability for "{$taxonomy}_{$term_id}"
		// this will be used until acf is converted to using the "term_"
		// prefix and so that the change will not break themes using
		// the old post_id method
		// see new-helper-functions.php for more info
		
		if (apply_filters('acf/keep_taxonomy_option', false)) {
			// a filter for people that have used 
			// get_option instead of get_field
			// when getting acf fields for terms
			// need to get the taxonomy form the id, but how
			acf_update_option( $post_id . '_' . $name, $value );
		}
		
		$return = update_metadata('term', $meta_id, $name, $value);
		
	} else {
		
		// modify prefix for hidden meta
		if( $hidden ) {
			
			$post_id = '_' . $post_id;
			$name = substr($name, 1);
			
		}
		
		$return = acf_update_option( $post_id . '_' . $name, $value );
		
	}
	
	
	// return
	return (boolean) $return;
	
}


/*
*  acf_delete_metadata
*
*  This function will delete a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$hidden (boolean)
*  @return	$return (boolean)
*/

function acf_delete_metadata( $post_id = 0, $name = '', $hidden = false ) {
	
	// vars
	$return = false;
	
	
	// add prefix for hidden meta
	if( $hidden ) {
		
		$name = '_' . $name;
		
	}
	
	
	// postmeta
	if( is_numeric($post_id) ) {
		
		$return = delete_metadata('post', $post_id, $name );
	
	// usermeta
	} elseif( substr($post_id, 0, 5) == 'user_' ) {
		
		$user_id = (int) substr($post_id, 5);
		
		$return = delete_metadata('user', $user_id, $name);
		
	// commentmeta
	} elseif( substr($post_id, 0, 8) == 'comment_' ) {
		
		$comment_id = (int) substr($post_id, 8);
		
		$return = delete_metadata('comment', $comment_id, $name);
	
	// options	
	} elseif (substr($post_id, 0, 5) == 'term_') {
		
		// this is for future use when the rest of acf is converted to using 
		// "term_" as a prefix for all term "post ID" values
		
		$term_id = (int) substr($post_id, 5);
		
		$return = delete_metadata('term', $comment_id, $name);
		
	} elseif (($term_id = acf_convert_post_id_to_term_id($post_id)) != 0) {
		
		// this is for backward compatability for "{$taxonomy}_{$term_id}"
		// this will be used until acf is converted to using the "term_"
		// prefix and so that the change will not break themes using
		// the old post_id method
		// see new-helper-functions.php for more info
		
		$return = delete_metadata('term', $comment_id, $name);
		
	} else {
		
		// modify prefix for hidden meta
		if( $hidden ) {
			
			$post_id = '_' . $post_id;
			$name = substr($name, 1);
			
		}
		
		$return = delete_option( $post_id . '_' . $name );
		
	}
	
	
	// return
	return $return;
	
}

?>