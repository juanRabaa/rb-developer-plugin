import React, { useState, useEffect } from "react";
import PostMetaField from 'COMPONENTS/PostMetaField';
import RBField from 'COMPONENTS/RBField';
const apiFetch = wp.apiFetch;
const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel, PluginSidebar } = wp.editPost;
const { useSelect, subscribe } = wp.data;
const { __ } = wp.i18n;
//
// wp.rbDeveloper = {
//     RBField
// };

console.log('TESTTESTTEST');
function parseObjectPropNames(source, mapping){
    Object.keys(mapping).forEach(( ogPropKey, i) => {
        if( source.hasOwnProperty(ogPropKey) ){
            let newPropKey = mapping[ogPropKey];
            let propChildMapping;

            // Check if inner properties need change
            if(Array.isArray(newPropKey)){
                propChildMapping = newPropKey.length === 2 ? newPropKey[1] : newPropKey[2];
                newPropKey = newPropKey.length === 2 ? newPropKey[0] : ogPropKey;
            }
            source[newPropKey] = source[ogPropKey];

            // It can remain with the same key when only the inner props need change
            if( ogPropKey !== newPropKey )
                delete source[ogPropKey];

            // Map inner properties keys
            if( propChildMapping )
                parseObjectPropNames(source[newPropKey], propChildMapping);
        }
    });
}

/**
*   Changes the PHP field data keys into the keys accepted by the JS components
*   The fieldData is modified directly.
*/
function pasePHPFieldData(fieldData){
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

const RBPostMetaFields = () => {
    const [registeredPostMetaFields, setRegisteredPostMetaFields] = useState(null);
    const postType = useSelect( (select) => select('core/editor').getCurrentPostType() );
    const fields = [];

    // Fetch custom fields
    useEffect( () => {
        if(postType){
            apiFetch( { path: `/rb/postsMetaFields/v1/postType/${wp.data.select('core/editor').getCurrentPostType()}` } )
                .then( (data) => {
                    setRegisteredPostMetaFields(data);
                });
        }
    }, [postType]);

    function getPositionComponent(metaFieldData) {
        switch (metaFieldData.panel.position) {
            case "document-settings-panel":
                return PluginDocumentSettingPanel;
                break;
            case "sidebar":
                return PluginSidebar;
                break;
            default:
                return PluginDocumentSettingPanel;
                break;
        }
    }

    if(registeredPostMetaFields){
        console.log('registeredPostMetaFields', registeredPostMetaFields);

        Object.keys(registeredPostMetaFields).forEach((metaKey) => {
            const metaFieldData = pasePHPFieldData(registeredPostMetaFields[metaKey]);

            const PositionComponent = getPositionComponent(metaFieldData);
            fields.push(
                <PositionComponent
                    name={metaKey}
                    title={metaFieldData.panel.title}
                    icon={metaFieldData.panel.icon}
                    className="custom-panel"
                >
                    <PostMetaField
                        {...metaFieldData.fields }
                    />
                </PositionComponent>
            );
        } );
    }

    return fields;
};

const metaFieldsRegisterUnsubscribe = subscribe(() => {
    const currentPostType = wp.data.select('core/editor').getCurrentPostType();

    if(currentPostType){
        metaFieldsRegisterUnsubscribe();
        registerPlugin( `rb-post-meta-fields`, {
            render: RBPostMetaFields,
        });
    }
});
