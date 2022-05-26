import { render } from "react-dom";
import React from "react";
import parsePHPFieldData from "HELPERS/parsePHPFieldData";
import { deepFreeze } from "HELPERS/helpers";
import WPObjectMetaField from 'COMPONENTS/WPObjectMetaField';
import $ from 'jquery';

let fieldsFetched = false;
const parsedFieldsConfig = {};

/**
*   This is async because it may use a fetch in the future.
*   @return {object}
*/
export async function getWPObjectsListFields(){
    if(fieldsFetched)
        return parsedFieldsConfig;

    const WPObjectsListFields = RBObjectsList?.fields ?? {};
    // const WPObjectsListFields = await apiFetch( {
    //     path: `/rb-fields/v1/wp-object/${RBObjectsList.objectSubtype}/${RBObjectsList.subtypeKind}`,
    // } );

    if(WPObjectsListFields){
        Object.keys(WPObjectsListFields).forEach((metakey) => {
            const metaFieldConfig = WPObjectsListFields[metakey];
            parsedFieldsConfig[metakey] = parsePHPFieldData(metaFieldConfig.field);
        } );
    }

    deepFreeze(parsedFieldsConfig);
    fieldsFetched = true;
    return parsedFieldsConfig;
}

/**
*   Renders the fields on the placeholders of the columns in the objects lists table.
*   @param {DOMElement} parent                                                  The element from where to get the fields
*                                                                               placeholders. A row can be passed through this element
*                                                                               to only generate the fields for a certain object row.
*/
export function renderColumnsFields({ parent = document }){
    $(".rb-field-col-placeholder", parent).each( function(index){
        const $placeholder = $(this);
        renderWPObjectMetaField($placeholder, {
            onChange: function({ value }){
                updatePlaceholderDataValue({ $placeholder, value });
            },
        });
    });
}

/**
*   Renders a WPObjectMetaField component on top of the given $placeholder.
*   Most of the optios will be fetch from the closest `.rb-object-col-field` if not provided.
*/
export async function renderWPObjectMetaField($placeholder, options){
    let {
        metakey,
        objectID,
        fieldData,
        onChange,
    } = options ?? {};

    const $dataParent = $placeholder.closest(".rb-object-col-field");
    metakey = metakey ?? $dataParent.data("metakey");
    objectID = objectID ?? $dataParent.data("objectid");
    const parsedFieldsConfig = await getWPObjectsListFields();
    fieldData = fieldData ?? parsedFieldsConfig[metakey];

    if(fieldData){
        render(
            <WPObjectMetaField
                {...fieldData }
                objectSubtype={RBObjectsList.objectSubtype}
                subtypeKind={RBObjectsList.subtypeKind}
                objectID={objectID}
                onChange={onChange}
            />,
            $placeholder[0]
        );
    }
}

/**
*   Returns the value of a column field for a given object.
*   @param {int} objectID
*   @param {string} metakey
*/
export function getObjectRowFieldValue({ objectID, metakey }){
    return getObjectRow({objectID}).find(`.rb-object-col-field[data-metakey="${metakey}"]`).attr("data-value");
}

/**
*   Returns the single key slug from the configuration. It is used by Wordpress to
*   generate rows ids among other things.
*   @param {string}
*/
export function getObjectSingleKey(){
    return RBObjectsList.objectSingle;
}

/**
*   @param {int} objectID
*/
export function getObjectRow({ objectID }){
    return $(`#${getObjectSingleKey()}-${objectID}`);
}

/**
*   The data-value is used to generate the fields in the quick edit box. It generally
*   gets updated after a change from the column field, so that the quick edit field
*   gets the latest value.
*   @param {string}
*/
export function updatePlaceholderDataValue({ $placeholder, value }){
    $placeholder.closest(".rb-object-col-field").attr("data-value", JSON.stringify(value));
}
