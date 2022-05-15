<?php

class RB_Customizer_Control{
	static public $controls = array();
	public $id;
    public $wp_customize_manager;
	public $control_class;
	public $options;
	public $settings = array();

	public function __construct($id, $wp_customize_manager, $control_class, $options, $settings){
		$this->id = $id;
		$this->settings = $settings;
		$this->control_class = $control_class;
        $settings_ids = array_keys( $settings );
        $options['settings'] = count($settings_ids) === 1 ? $settings_ids[0] : $settings_ids;
		$this->options = $options;
        $this->wp_customize_manager = $wp_customize_manager;
        $this->add_settings($settings);
        $wp_customize_manager->add_control( new $control_class($wp_customize_manager, $id, $options) );
		self::$controls[] = $this;
	}

    // REVIEW: Should this be done from a section? settings are global entities. But does it make sense
    // to modify the same setting through multiple section?
	public function add_settings( $settings ){
		$this->settings = array();
		foreach ( $settings as $setting_id => $setting_data ){
			$this->settings[] = new RB_Customizer_Setting($setting_id, $this->wp_customize_manager, $setting_data['options'], $setting_data['selective_refresh'] ?? array());
		}
		return $this->settings;
	}

	//Returns every setting the control is associoted to that doesnt have the selective refresh activated
	public function settings_without_selective_refresh( $id_only = false ){
		$result = array_filter($this->settings, fn($setting) => !$setting->selective_refresh['activated'] );
		if ( $id_only )
			$result = array_map( fn($setting) => $setting->id, $result  );
		return $result;
	}

	//Returns every setting the control is associoted to that has the selective refresh activated
	public function settings_with_selective_refresh( $id_only = false ){
		$result = array_filter( $this->settings, fn($setting) => ($setting->selective_refresh['activated'] && !$setting->selective_refresh['prevent']) );
		if ( $id_only )
			$result = array_map( $result, fn($setting) => $setting->id );
		return $result;
	}

}
