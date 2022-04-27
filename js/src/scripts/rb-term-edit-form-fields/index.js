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
    const taxonomy = wp.url.getQueryArg(window.location.href, "taxonomy");
    const registeredTermMetaFields = await apiFetch( {
        path: `/rb/termsMetaFields/v1/taxonomy/${taxonomy}` ,
    } );
    console.log("registeredTermMetaFields", taxonomy, registeredTermMetaFields);

    $(document).ready( function(){
        if(registeredTermMetaFields){
            Object.keys(registeredTermMetaFields).forEach((metaKey) => {
                const termMetaFieldConfig = registeredTermMetaFields[metaKey];
                const fieldData = parsePHPFieldData(termMetaFieldConfig.field);
                const $rowPlaceholder = $(`#rb-field-placeholder__${metaKey}`);
                let metaValue = JSON.parse($rowPlaceholder.attr("data-value"));
                render(<RBFinalField {...fieldData } value={metaValue} />, $rowPlaceholder[0]);
            } );
        }
    });
}

render_fields();
