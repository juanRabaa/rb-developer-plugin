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
    const registeredPostMetaFields = await apiFetch( {
        path: `/rb-fields/v1/post_type/${RBPlugin.current_post_type}` ,
    } );
    console.log("registeredPostMetaFields", RBPlugin.current_post_type, registeredPostMetaFields);

    $(document).ready( function(){
        if(registeredPostMetaFields){
            Object.keys(registeredPostMetaFields).forEach((metaKey) => {
                const postMetaFieldConfig = registeredPostMetaFields[metaKey];
                const fieldData = parsePHPFieldData(postMetaFieldConfig.field);
                const $rowPlaceholder = $(`#rb-field-placeholder__${metaKey}`);
                if(!$rowPlaceholder.length)
                    return;

                let metaValue = JSON.parse($rowPlaceholder.attr("data-value"));
                render(<RBFinalField {...fieldData } value={metaValue} />, $rowPlaceholder[0]);
            } );
        }
    });
}

render_fields();
