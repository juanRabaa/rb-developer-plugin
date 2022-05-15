import { render } from "react-dom";
import React from "react";
import RBFinalField from 'COMPONENTS/RBFinalField';
import parsePHPFieldData from "HELPERS/parsePHPFieldData";
import $ from 'jquery';
const apiFetch = wp.apiFetch;

/**
*   Replaces the placeholder of fields for the REACT component that manages the
*   meta value.
*/
async function render_fields(){
    const registeredControls = RBFieldCustomizerControl?.fields;
    console.log("registeredControls", registeredControls);
    $(document).ready( function(){
        if(registeredControls){
            Object.keys(registeredControls).forEach((metaKey) => {
                const fieldConfig = parsePHPFieldData(registeredControls[metaKey].field);
                const $rowPlaceholder = $(`#rb-customizer-field-placeholder__${metaKey}`);
                if(!$rowPlaceholder.length)
                    return;

                let metaValue = JSON.parse($rowPlaceholder.attr("data-value"));
                render(<RBFinalField {...fieldConfig } value={metaValue} onChange={
                    ({value: newValue}) => {
                        $rowPlaceholder.next().val(JSON.stringify(newValue)).trigger("change");
                    }
                }/>, $rowPlaceholder[0]);
            } );
        }
    });
}

wp.customize.bind( 'ready', function() {
    render_fields();
    wp.customize.previewer.bind( 'ready', function( message ) {
        console.info( 'Preview is loaded' );
    });
});
