import { render } from "react-dom";
import React from "react";
import WPObjectMetaField from 'COMPONENTS/WPObjectMetaField';
import parsePHPFieldData from "HELPERS/parsePHPFieldData";
import $ from 'jquery';
const apiFetch = wp.apiFetch;

/**
*   Replaces the placeholder of fields for the REACT component that manages the
*   meta value.
*/
async function render_fields(){
    const registeredObjectSubtypeKindMetaFields = await apiFetch( {
        path: `/rb-fields/v1/${RBObjectsList.objectSubtype}/${RBObjectsList.subtypeKind}`,
    } );

    const parsedFieldsConfig = {};

    if(registeredObjectSubtypeKindMetaFields){
        Object.keys(registeredObjectSubtypeKindMetaFields).forEach((metakey) => {
            const metaFieldConfig = registeredObjectSubtypeKindMetaFields[metakey];
            parsedFieldsConfig[metakey] = parsePHPFieldData(metaFieldConfig.field);
        } );
    }

    $(document).ready( function(){
        $(".rb-field-col-placeholder").each( function(index){
            const $placeholder = $(this);
            console.log("$placeholder", $placeholder);

            if(!$placeholder.length)
                return;

            const metakey = $placeholder.data("metakey");
            const objectID = $placeholder.data("objectid");
            const fieldData = parsedFieldsConfig[metakey];

            if(fieldData){
                render(
                    <WPObjectMetaField
                        {...fieldData }
                        objectSubtype={RBObjectsList.objectSubtype}
                        subtypeKind={RBObjectsList.subtypeKind}
                        objectID={objectID}
                    />,
                    $placeholder[0]
                );
            }

        });
    });
}

render_fields();
