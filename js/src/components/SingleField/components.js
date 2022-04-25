const {
    TextControl,
    __experimentalNumberControl: NumberControl,
} = wp.components;
import RBAttachmentControl from '../RBAttachmentControl';

const defaultComponents = {
    text: {
        component: TextControl,
        propsMapping: {
            name: "name",
            value: "value",
            onChange: "onChange",
        },
    },
    // https://developer.wordpress.org/block-editor/reference-guides/components/number-control
    number: {
        component: NumberControl,
        propsMapping: {
            name: "name",
            value: "value",
            onChange: "onChange",
        },
    },
    attachments: {
        component: RBAttachmentControl,
        propsMapping: {
            name: "name",
            value: "attachmentsIDs",
            onChange: "onChange",
            filterOnChange: ({ attachments }) => attachments,
        },
    },
};

export default function getComponent({ name }){
    const components = wp.hooks.applyFilters( 'rbFieldsComponentsList', defaultComponents );
    return components[name];
}
