import { render } from "react-dom";
import React from "react";
import RBFinalField from 'COMPONENTS/RBFinalField';
import parsePHPFieldData from "HELPERS/parsePHPFieldData";
import $ from 'jquery';

/**
*   Replaces the placeholder of fields for the REACT component that manages the
*   meta value.
*/
async function render_fields(){
    const registeredUserMetaFields = RBUserScript.fields;
    console.log("registeredUserMetaFields", registeredUserMetaFields);

    $(document).ready( function(){
        if(registeredUserMetaFields){
            Object.keys(registeredUserMetaFields).forEach((metaKey) => {
                const userMetaFieldConfig = registeredUserMetaFields[metaKey];
                const fieldData = parsePHPFieldData(userMetaFieldConfig.field);
                const $rowPlaceholder = $(`#rb-user-field-placeholder__${metaKey}`);
                if(!$rowPlaceholder.length)
                    return;

                let metaValue = JSON.parse($rowPlaceholder.attr("data-value"));
                render(<RBFinalField {...fieldData } value={metaValue} />, $rowPlaceholder[0]);
            } );
        }
    });
}

render_fields();
