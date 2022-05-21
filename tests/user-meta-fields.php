<?php

add_action( "rb-fields-object-user-ready", function() use ($test_fields_data, $simple_gallery_field){
	RB_User_Meta_Fields_Manager::add_field(array(
		"meta_key"      => "single_gallery",
	    "panel"         => array(
	        "title"         => "Main Gallery",
	        "icon"          => "format-gallery",
	        "position"      => "document-settings-panel",
	    ),
		"roles"			=> array("editor"),
		// "capabilities"	=> array("edit_others_posts"),
		"column"		=> array(
			"title"			=> "Editor column",
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

	RB_User_Meta_Fields_Manager::add_field(array(
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
