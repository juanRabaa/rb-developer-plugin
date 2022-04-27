import React, { useState, useEffect } from "react";
import PostMetaField from 'COMPONENTS/PostMetaField';
import RBField from 'COMPONENTS/RBField';
import pasePHPFieldData from "HELPERS/pasePHPFieldData";
const apiFetch = wp.apiFetch;
const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel, PluginSidebar } = wp.editPost;
const { useSelect, subscribe } = wp.data;
const { __ } = wp.i18n;

const metaFieldsRegisterUnsubscribe = subscribe(() => {
    const currentPostType = wp.data.select('core/editor').getCurrentPostType();

    if(currentPostType){
        metaFieldsRegisterUnsubscribe();
        registerPlugin( `rb-post-meta-fields`, {
            render: RBPostMetaFields,
        });
    }
});

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

    console.log('registeredPostMetaFields', registeredPostMetaFields);
    if(registeredPostMetaFields){
        Object.keys(registeredPostMetaFields).forEach((metaKey) => {
            const postMetaFieldConfig = registeredPostMetaFields[metaKey];
            const fieldData = pasePHPFieldData(postMetaFieldConfig.field);

            const PositionComponent = getPositionComponent(postMetaFieldConfig);
            fields.push(
                <PositionComponent
                    name={metaKey}
                    title={postMetaFieldConfig.panel.title}
                    icon={postMetaFieldConfig.panel.icon}
                    className="custom-panel"
                >
                    <PostMetaField
                        {...fieldData }
                    />
                </PositionComponent>
            );
        } );
    }

    return fields;
};
