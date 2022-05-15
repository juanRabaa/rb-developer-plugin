<?php

// TODO: Setting config should be optional, as an already defined setting could be used
class RB_Customizer_Control{
	static public $controls = array();
	public $id;
    public $wp_customize_manager;
	public $control_class;
	public $options;
	public $setting = null;

	public function __construct($id, $wp_customize_manager, $control_class, $setting_config, $options){
		$this->id = $id;
		$this->control_class = $control_class;
		$this->options = $options;
        $this->wp_customize_manager = $wp_customize_manager;
        $this->add_setting($setting_config);
        $wp_customize_manager->add_control( new $control_class($wp_customize_manager, $id, $options) );
		self::$controls[] = $this;
	}

	public function add_setting($setting_config){
		if(!$setting_config)
			return;
		$setting_id = $setting_config["id"] ?? $this->id; // Use control id as the settings id if none provided
		$this->options['settings'] = $setting_id;
		$this->setting = new RB_Customizer_Setting($setting_id, $this->wp_customize_manager, $setting_config['options'], $setting_config['selective_refresh'] ?? array());
		return $this->setting;
	}
}
