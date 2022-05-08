const { useSelect, useDispatch } = wp.data;
import RBField from 'COMPONENTS/RBField';

// TODO: Filter with hooks the meta name based on the current language
/**
*   Used inside a gutenberg core/editor context to add a series of controls to modify
*   one of the post meta values
*/
export default function GutenbergPostMetaField(props){
    const {
        name,
        onChange: passedOnChange,
        ...metaFieldProps
    } = props;
    const { editPost } = useDispatch( 'core/editor' );

    const metaValue = useSelect( (select ) => {
        return select( 'core/editor' ).getEditedPostAttribute('meta')?.[name];
    });

    const updateMetaValue = ({ value: newMetaValue }) => {
        console.log("newMetaValue", name, newMetaValue);
        editPost({
            meta: {
                [name]: newMetaValue,
            },
        });
    };

    return (
        <RBField
            {...props}
            value={metaValue}
            onChange = {updateMetaValue}
        />
    )
}
