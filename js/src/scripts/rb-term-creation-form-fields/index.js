import { render } from "react-dom";
import React from "react";
import RBFinalField from 'COMPONENTS/RBFinalField';
import parsePHPFieldData from "HELPERS/parsePHPFieldData";
import $ from 'jquery';
const apiFetch = wp.apiFetch;

 /* global validateForm */
function cleanFieldValue({ value, setValue }){
    $('#submit').on( 'click', function(){
        const form = $(this).parents('form');
        if(validateForm(form))
            setValue(null);
    });
}

/**
*   Replaces the placeholder of fields for the REACT component that manages the
*   meta value.
*/
async function render_fields(){
    const taxonomy = wp.url.getQueryArg(window.location.href, "taxonomy");
    const registeredTermMetaFields = await apiFetch( {
        path: `/rb-fields/v1/taxonomy/${taxonomy}` ,
    } );
    console.log("registeredTermMetaFields", taxonomy, registeredTermMetaFields);

    $(document).ready( function(){
        if(registeredTermMetaFields){
            Object.keys(registeredTermMetaFields).forEach((metaKey) => {
                const termMetaFieldConfig = registeredTermMetaFields[metaKey];
                const fieldData = parsePHPFieldData(termMetaFieldConfig.field);
                const $rowPlaceholder = $(`#rb-tax-field-placeholder__${metaKey}`);
                render(<RBFinalField {...fieldData } onRender={cleanFieldValue}/>, $rowPlaceholder[0]);
            } );
        }
    });
}

render_fields();
