<?php

class RB_Customizer_API{
	public $wp_customize_manager;
	public $sections = array();

	public function __construct($wp_customize_manager){
		$this->wp_customize_manager = $wp_customize_manager;
	}

	public function add_section($name, $options, $selective_refresh = array()){
		$section = new RB_Customizer_Section($this->wp_customize_manager, $name, $options, $selective_refresh);
		$this->sections[] = $section;
		return $section;
	}

	public function add_panel($name, $options){
		if($this->wp_customize_manager)
			$this->wp_customize_manager->add_panel( $name, $options );
		return $this;
	}

	public function get_section( $id ){
		foreach( $this->sections as $section ){
			if ( $section->id == $id )
				return $section;
		}
		return null;
	}
}
