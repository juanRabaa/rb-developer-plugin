<?php

$test_fields_data = array(
    // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
    "repeater"      => array(
        "dynamic_title"         => "title", // Name of the field to get value from
        "collapse"              => true,
        "collapse_open"         => false,
        "accordion"             => false,
        "sortable"              => true,
        "max"                   => 10,
        "layout"                => "tabs",
        "labels"                => array(
            "empty"         => "No hay galerias cargadas. Empeza ya!",
            "item_title"    => "Gallery (%n)", // Default title of the field ( %n is replaced with the position of the current item)
            "max_reached"   => "Paga el premium para agregar mas galerias! (re rata el dev)",
        ),
    ),
    "description"   => "Muchas galerias. Por las dudas.",
    // "label"         => "Test Repeater",
    "fields"    => array(
        array(
            "name"          => "title",
            "label"         => "Titulo",
            "type"          => "string",
            "component"     => "text",
            "default_value" => "DEFAULT",
        ),
        array(
            "name"          => "description",
            "label"         => "Descripcion",
            "type"          => "string",
            "component"     => "TextareaControl",
        ),
        array(
            "name"          => "attachment",
            "label"         => "Galeria",
            "type"          => "array",
            "items" => array(
                "type"  => "integer",
            ),
            "component"     => "attachments",
            "component_props"    => array(
                "gallery"   => true,
            ),
        ),
        array(
            "name"              => "background",
            "label"             => "Color de fondo",
            "type"              => "string",
            "component"         => "ColorPalette",
            "default_value"     => "#fff",
            "component_props"    => array(
                "colors"    => array(
                    array( "name"  => "red", "color"    => "#f00" ),
                    array( "name"  => "white", "color"  => "#fff" ),
                    array( "name"  => "blue", "color"   => "#00f" ),
                ),
            ),
        ),
        array(
            "name"              => "slider_type",
            "label"             => "Slider type",
            "component"         => "RadioControl",
            "defaultValue"      => "second",
            "component_props"    => array(
                "min"   => 10,
                "max"   => 20,
                // "label" => "Slider type",
                "help"  => "Choose!",
                "options"   => array(
                    array(
                        "label"     => "First Option",
                        "value"    => "first",
                    ),
                    array(
                        "label"     => "Second Option",
                        "value"    => "second",
                    ),
                ),
            ),
            "propsMapping"      => array(
                "value" => "selected",
            ),
        ),
    ),
);
