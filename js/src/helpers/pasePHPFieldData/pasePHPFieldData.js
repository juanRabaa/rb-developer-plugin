import parseObjectPropNames from "HELPERS/parseObjectPropNames";

/**
*   Changes the PHP field data keys into the keys accepted by the JS components
*   The fieldData is modified directly.
*/
export default function pasePHPFieldData(fieldData){
    const fieldPropsMapping = {
        "default_value": "defaultValue",
    };

    const repeaterPropsMapping = {
        "dynamic_title": "dynamicTitle",
        "collapse_open": "collapseOpen",
        "empty_message": "emptyMessage",
        "labels": ["labels", {
            "max_reached": "maxReached",
            "item_title": "itemTitle",
        }],
    };

    parseObjectPropNames(fieldData, fieldPropsMapping);

    if(typeof fieldData.repeater === "object"){
        parseObjectPropNames(fieldData.repeater, repeaterPropsMapping);
    }

    // Parse child fields data
    if( fieldData?.fields?.length ){
        fieldData.fields.forEach(( childFieldData, i ) => {
            pasePHPFieldData(childFieldData);
        });
    }

    return fieldData;
}
