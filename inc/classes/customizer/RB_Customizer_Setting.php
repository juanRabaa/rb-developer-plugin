<?php

class RB_Customizer_Setting{
	static public $settings = array();
	public $id;
    public $wp_customize_manager;
	public $options = array();
	public $selective_refresh = array(
        "selector"              => null,
		"activated"           => false,
		"prevent"             => false,
        "render_callback"       => null,
	);

	public function __construct($id, $wp_customize_manager, $options, $selective_refresh = array()){
		$this->id = $id;
		$this->options = $options;
        $this->selective_refresh = sanitize_selective_refresh_args($selective_refresh, array(
            "render_callback"   => function(){ echo get_theme_mod( $this->id, ""); },
        ));
        $this->wp_customize_manager = $wp_customize_manager;
        $this->wp_customize_manager->add_setting($this->id, $this->options);
        $this->add_selective_refresh_partials();
		self::$settings[] = $this;
	}

    public function add_selective_refresh_partials(){
        $settings_selective_refresh = get_all_selective_refresh($this->selective_refresh);
        //Add partial to every selector for wich this setting has a refresh configuration
        foreach( $settings_selective_refresh as $selector => $selective_refresh){
            $args = $selective_refresh;
            $args["settings"] = array($setting->id);
            $this->wp_customize_manager->selective_refresh->add_partial($setting->id . md5($selector), $args);
        }
    }

	public function has_selective_refresh(){
		return $this->selective_refresh['activated'] && !$this->selective_refresh['prevent'];
	}
}
