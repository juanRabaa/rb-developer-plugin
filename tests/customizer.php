<?php

add_action( "customize_register", function( $wp_customize ) use ($test_fields_data){
    $customizer_api = new RB_Customizer_API($wp_customize);

    // =========================================================================
    // GENERAL SETTINGS
    // =========================================================================
    $customizer_api->add_panel(
		"gen-general",
		array(
			"priority"       => 1,
			"capability"     => "edit_theme_options",
			"theme_supports" => "",
			"title"          => __("Ajustes Generales", "genosha-web"),
			"description"    => __("Configuración general", "genosha-web"),
		),
	);

    // =========================================================================
    // ANIMATIONS
    // =========================================================================
    $customizer_api->add_section(
        "gen-general-animations",
        array(
            "title"     => "Animaciones",
            "priority"  => 1,
            "panel"  	=> "gen-general",
        ),
        array(
            // "activated" 		=> true,
            // "selector"			=>	"#main-grid",
        ),
    )
    ->add_control(//Control creation
        "gen-general-animations-active",//id
        "WP_Customize_Color_Control",//control class
        array(//Settings creation
            // "id"                => "gen-general-animations-active",
            "options"           => array(
                "transport" => "refresh",
                "default"	=> true,
            ),
            "selective_refresh" => array(
                "activated" 			=> true,
            ),
        ),
        array(//Control options
            "label"      => __( "Header Color", "mytheme" ),
        ),
    )
    ->add_control(//Control creation
        "rb_field_customizer_test",//id
        "RB_Field_Customizer_Control",//control class
        array(//Settings creation
            // "id"                => "gen-general-animations-active",
            "options"           => array(
                "transport" => "refresh",
                "default"	=> true,
            ),
            "selective_refresh" => array(
                "activated" 			=> true,
            ),
        ),
        array(//Control options
            "label"         => "Texto prueba",
    	    "field"        => array(
    	        "type"          => "string",
    	        // "label"         => "Test Single",
    	        // "description"   => "Test Single",
    	        "component"     => "text",
    	    ),
        ),
    )
    ->add_control(//Control creation
        "rb_field_customizer_test2",//id
        "RB_Field_Customizer_Control",//control class
        array(//Settings creation
            // "id"                => "gen-general-animations-active",
            "options"           => array(
                "transport" => "refresh",
                "default"	=> true,
            ),
            "selective_refresh" => array(
                "activated" 			=> true,
            ),
        ),
        array(//Control options
            "label"         => "Galerias",
    	    "field"            => $test_fields_data,
        ),
    );
});
