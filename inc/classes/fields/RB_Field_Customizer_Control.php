<?php
if(!class_exists('WP_Customize_Control'))
	return;

class RB_Field_Customizer_Control extends WP_Customize_Control {
	use Initializer;
	static protected $fields_manager = null;

	public function __construct($manager, $id, $args = array()){
		parent::__construct($manager, $id, $args);
		self::init();
		$args["meta_key"] = $id;
		self::$fields_manager->add_field($args);
		add_filter( "pre_set_theme_mod_{$id}", array($this, "sanitize_value_to_save"), 10, 2);
	}

	/**
	*	Generate the fields manager and enqueue neccessary scripts
	**/
	static protected function on_init(){
		self::$fields_manager = new RB_Fields_Manager();
		add_action( 'customize_controls_enqueue_scripts', array(self::class, "enqueue_scripts") );
	}

	/**
	*	Decodes the value of the theme_mod setting value. Used in the
	*	filter pre_set_theme_mod_{$id}
	*/
	public function sanitize_value_to_save($value, $old_value){
		$value = json_decode(wp_unslash($value), true);
		return $value;
	}

	static public function enqueue_scripts($hook){
        wp_enqueue_media();
        wp_enqueue_script( 'rb-field-customizer-control', RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-field-customizer-control/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post', "customize-controls"], false );
		wp_localize_script( "rb-field-customizer-control", "RBFieldCustomizerControl", array(
			"fields"	=> self::$fields_manager->get_registered_fields(),
		));
    }

	/**
	*	Renders the placeholder for the REACT field. Also renders a hidden input
	*	because the customizer API doesn't work with the inputs added after its ready
	*/
	protected function render_content() {
		$value = $this->value();
		?>
		<div class="rb-field">
			<?php if($this->label): ?>
				<label for="<?php echo esc_attr( $this->id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
			<?php endif; ?>
			<?php if ( !$this->description ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
			<div id="rb-customizer-field-placeholder__<?php echo esc_attr( $this->id ); ?>" class="rb-field-placeholder" data-value="<?php echo esc_attr( json_encode($value) ); ?>">
				<p><span class="spinner is-active"></span>Loading</p>
			</div>
			<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( json_encode($value) ); ?>"/>
		</div>
		<?php
	}
}
