<?php

class RB_Customizer_Section{
	static public $sections = array();
	public $wp_customize_manager;
	public $id;
	public $options = array();
	public $controls = array();
	public $selective_refresh = array(
		'activated' => false,
		'selector'	=> '',
	);

	public function __construct($wp_customize_manager, $id, $options, $selective_refresh = array()){
		$this->id = $id;
        $this->wp_customize_manager = $wp_customize_manager;
		$this->options = $options;
        $this->wp_customize_manager->add_section($id,$options);
		$this->selective_refresh = sanitize_selective_refresh_args($selective_refresh, $this->selective_refresh);
		// We want this to run after all controls and settings have been defined.
		add_action("customize_register", array($this, "add_selective_refresh_partials"), 999999999999);
		array_push(self::$sections, $this);
	}

    /**
    *   Associating the section's selective refresh to any setting that doesn't have their own
    */
    public function add_selective_refresh_partials(){
        $dependent_settings = $this->settings_without_selective_refresh();
        if ( ($this->selective_refresh['activated'] ?? false) && !empty($dependent_settings) ){
            $selectors = get_all_selective_refresh($this->selective_refresh);
            foreach( $selectors as $selector => $selective_refresh){
                $args = $selective_refresh;
                $args["settings"] = array($dependent_settings);
                $this->wp_customize_manager->selective_refresh->add_partial($this->id . md5($selector), $args);
            }
        }
    }

	public function add_control($id, $control_class, $setting, $options){
		$options['section'] = $this->id;
		$this->controls[] = new RB_Customizer_Control($id, $this->wp_customize_manager, $control_class, $setting, $options);
		return $this;
	}

	//For every control it has, returns the settings that doesnt have selective refresh activated
	public function settings_without_selective_refresh(){
		$settings = [];
		foreach ($this->controls as $control) {
			if(!$control->setting?->has_selective_refresh())
				$settings[] = $control->setting->id;
		}
        return $settings;
	}
}
