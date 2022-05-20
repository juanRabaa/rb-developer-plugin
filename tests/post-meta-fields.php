<?php

// Register Custom Post Type
add_action( 'init', function() {
	$labels = array(
		'name'                  => _x( 'Customs', 'Custom General Name', 'rb_development' ),
		'singular_name'         => _x( 'Custom', 'Custom Singular Name', 'rb_development' ),
		'menu_name'             => __( 'Customs', 'rb_development' ),
		'name_admin_bar'        => __( 'Custom', 'rb_development' ),
		'archives'              => __( 'Item Archives', 'rb_development' ),
		'attributes'            => __( 'Item Attributes', 'rb_development' ),
		'parent_item_colon'     => __( 'Parent Item:', 'rb_development' ),
		'all_items'             => __( 'All Items', 'rb_development' ),
		'add_new_item'          => __( 'Add New Item', 'rb_development' ),
		'add_new'               => __( 'Add New', 'rb_development' ),
		'new_item'              => __( 'New Item', 'rb_development' ),
		'edit_item'             => __( 'Edit Item', 'rb_development' ),
		'update_item'           => __( 'Update Item', 'rb_development' ),
		'view_item'             => __( 'View Item', 'rb_development' ),
		'view_items'            => __( 'View Items', 'rb_development' ),
		'search_items'          => __( 'Search Item', 'rb_development' ),
		'not_found'             => __( 'Not found', 'rb_development' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'rb_development' ),
		'featured_image'        => __( 'Featured Image', 'rb_development' ),
		'set_featured_image'    => __( 'Set featured image', 'rb_development' ),
		'remove_featured_image' => __( 'Remove featured image', 'rb_development' ),
		'use_featured_image'    => __( 'Use as featured image', 'rb_development' ),
		'insert_into_item'      => __( 'Insert into item', 'rb_development' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'rb_development' ),
		'items_list'            => __( 'Items list', 'rb_development' ),
		'items_list_navigation' => __( 'Items list navigation', 'rb_development' ),
		'filter_items_list'     => __( 'Filter items list', 'rb_development' ),
	);
	$args = array(
		'label'                 => __( 'Custom', 'rb_development' ),
		'description'           => __( 'Custom Description', 'rb_development' ),
		'labels'                => $labels,
		'supports'              => ['title', 'thumbnail', 'excerpt', "custom-fields"],
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'show_in_rest'			=> true,
		'capability_type'       => 'page',
	);
	register_post_type( 'rb_meta_test_postype', $args );
}, 0 );

add_action( "rb-fields-object-post-ready", function() use ($test_fields_data, $simple_gallery_field){
	/**********************************************************
	*   POST META FIELDS TEST
	***********************************************************/
	RB_Post_Meta_Fields_Manager::add_field(array(
	    // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
	    "meta_key"      => "single_meta_field",
	    "post_type"     => ["post", "rb_meta_test_postype"],
	    "single"        => true,
	    "panel"         => array(
	        "title"         => "Single Value Field",
	        "icon"          => "format-gallery",
	        "position"      => "document-settings-panel",
	    ),
	    "field"        => array(
	        "type"          => "string",
	        "label"         => "Test Single",
	        "description"   => "Test Single",
	        "component"     => "text",
	    ),
	));

	RB_Post_Meta_Fields_Manager::add_field(array(
	    // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
	    "meta_key"      => "single_meta_field",
	    "post_type"     => "page",
	    "single"        => true,
	    "panel"         => array(
	        "title"         => "Single Value Field",
	        "icon"          => "format-gallery",
	        "position"      => "document-settings-panel",
	    ),
	    "field"        => array(
	        "type"          => "string",
	        "label"         => "Test Single",
	        "description"   => "Test Single",
	        "component"     => "te78xt",
	    ),
	));

	RB_Post_Meta_Fields_Manager::add_field(array(
	    // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
	    "meta_key"      => "single_gallery",
	    "post_type"     => "rb_meta_test_postype",
		"register"		=> true,
	    "single"        => true,
	    "panel"         => array(
	        "title"         => "Main Gallery",
	        "icon"          => "format-gallery",
	        "position"      => "document-settings-panel",
	    ),
		"column"		=> array(
			"title"			=> "Main Gallery",
			// "content"		=> "Columna",
		),
	    "field"        => array(
			"type"          => "array",
            "items" => array(
                "type"  => "integer",
            ),
	        // "label"         => "Test Single",
	        // "description"   => "Test Single",
	        "component"     => "attachments",
			"component_props"    => array(
				"gallery"   => true,
			),
	    ),
	));

	RB_Post_Meta_Fields_Manager::add_field(array(
	    "meta_key"      => "meta_test",
	    "post_type"     => ["post", "rb_meta_test_postype"],
	    "single"        => true,
	    "type"          => "object",
		// "column"		=> array(
		// 	"title"			=> "Columna",
		// 	// "content"		=> "Columna",
		// ),
	    "panel"         => array(
	        "title"         => "Galerias",
	        "icon"          => "format-gallery",
	        "position"      => "document-settings-panel",
	    ),
	    "field"        => $test_fields_data,
	));

	RB_Post_Meta_Fields_Manager::add_field(array(
	    "meta_key"      => "sidebar_test_meta",
	    "post_type"     => "post",
	    "single"        => true,
	    "type"          => "object",
	    "panel"         => array(
	        "title"         => "Galerias",
	        "icon"          => "format-gallery",
	        "position"      => "sidebar",
	    ),
	    "field"        => $test_fields_data,
	));
});
