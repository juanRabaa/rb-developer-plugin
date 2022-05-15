<?php
// REVIEW: this functions existts because both the RB_Customizer_Section and
// RB_Customizer_Setting need this logic. Should this be moved into a trait?
function get_all_selective_refresh($selective_refresh){
    if(!isset($selective_refresh['selector']))
        return array();

    if( is_array($selective_refresh['selector']) )
        return $selective_refresh['selector'];

    return array(
        $selective_refresh['selector'] => $selective_refresh,
    );
}

function sanitize_selective_refresh_args($args, $defaults = array()){
    $selective_refresh = array_merge( array(
        "selector"              => null,
        "activated"           => false,
        "prevent"             => false,
        "render_callback"       => null,
    ), $defaults);

    $selective_refresh = array_merge($selective_refresh, $args);
    if(!isset($selective_refresh['selector']) || !is_array($selective_refresh['selector']) || !is_string($selective_refresh['selector']))
        return false;

    function sanitize_single($selector, $selector_selective_refresh){
        if(!is_array($selective_refresh))
            return null;
        //Selector
        $selective_refresh['selector'] = $selector;
        //Callback. If non was given, a default one will be stored
        // $selective_refresh['has_user_callback'] = is_callable($selective_refresh['render_callback'] ?? null);
        // The default callback prints the setting value
        // if(!$selective_refresh['has_user_callback'])
        //     $selective_refresh['render_callback'] = function(){ echo get_theme_mod( $this->id, ""); };

        return $selective_refresh;
    }

    // =============================================================================
    // MULTIPLE SELECTORS
    // =============================================================================
    // Has different selective refresh config for each selector
    if(is_array($selective_refresh['selector'])){
        foreach($selective_refresh['selector'] as $selector => $selector_selective_refresh){
            sanitize_single($selector, $selector_selective_refresh);
        }
    }
    // =============================================================================
    // SINGLE SELECTOR
    // =============================================================================
    // $selective_refresh has the config for the selector refresh
    else{
        sanitize_single($selective_refresh['selector'], $selective_refresh);
    }
}
