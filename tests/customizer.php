<?php

add_action( 'customize_register', function( $wp_customize ) {
    $customizer_api = new RB_Customizer_API($wp_customize);

    // =========================================================================
    // GENERAL SETTINGS
    // =========================================================================
    $customizer_api->add_panel(
		'gen-general',
		array(
			'priority'       => 1,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => __('Ajustes Generales', 'genosha-web'),
			'description'    => __('Configuración general', 'genosha-web'),
		),
	);

    // =========================================================================
    // ANIMATIONS
    // =========================================================================
    $customizer_api->add_section(
        'gen-general-animations',
        array(
            'title'     => 'Animaciones',
            'priority'  => 1,
            'panel'  	=> 'gen-general',
        ),
        array(
            // 'activated' 		=> true,
            // 'selector'			=>	"#main-grid",
        ),
    )
    ->add_control(//Control creation
        'gen-general-animations-active',//id
        'WP_Customize_Color_Control',//control class
        array(//Settings creation
            'gen-general-animations-active' => array(
                'options' => array(
                    'transport' => 'refresh',
                    'default'	=> true,
                ),
                'selective_refresh' => array(
                    'activated' 			=> true,
                ),
            ),
        ),
        array(//Control options
            'label'      => __( 'Header Color', 'mytheme' ),
        ),
    );
});
