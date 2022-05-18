import { useEffect, useRef } from "react";
import FieldsGroup from 'COMPONENTS/FieldsGroup';
import SingleField from 'COMPONENTS/SingleField';
import RepeaterField from 'COMPONENTS/RepeaterField';
import $ from "jquery";

/**
*   Works as a factory, generating the right kind of field component (single, group or repeater)
*   based on the props.
*   It renders a hidden input that contains the value of the component
*/
export default function RBField(props){
    let {
        name,
        value: passedValue,
        defaultValue,
        label,
        description,
        repeater,
        forceGroup,
        fields = [],
        onChange: passedOnChange,
        component,
        componentProps,
        propsMapping,
        parent,
    } = props;

    const valueInputRef = useRef(null);
    const valueChangeTimes = useRef(0);
    const isSingle = component || (fields?.length === 1 && fields[0].component);
    let fieldType = "single";
    let value = passedValue;

    /**
    *   Trigger input change on value change
    *   This makes the field compatible with other js apis that work listening
    *   to the input change, like the customizer panel, or the media popup attachment
    *   metas.
    */
    useEffect( () => {
        if(valueInputRef.current && valueChangeTimes.current > 0)
            $(valueInputRef.current).trigger("change");
        valueChangeTimes.current = valueChangeTimes.current + 1;
    }, [value]);

    if(!repeater && !isSingle && fields?.length === 1 && !fields[0].name && fields[0].repeater){
        repeater = fields[0].repeater;
        fields = fields[0].fields;
    };

    if(repeater)
        fieldType = "repeater";
    else if(!isSingle || ( fields?.length === 1 && fields[0].name )){
        fieldType = "group";
    }

    const onChange = (data) => {
        if (passedOnChange)
            passedOnChange({...data, fieldType});
    };

    const fieldData = {
        depth: parent ? parent.depth + 1 : 0,
    };

    const commonProps = {
        fieldData,
        label,
        description,
        value,
        onChange,
        name,
        childFieldProps: {
            parent: fieldData,
        },
    };

    // Single props
    let singleProps = {
        ...commonProps,
        component,
        componentProps,
        propsMapping,
        defaultValue,
    };

    if(isSingle && !component){
        singleProps = {
            ...singleProps,
            ...fields[0],
        };
    }
    // END Single props

    // Repeater props
    let repeaterProps = {
        fields,
        component,
        componentProps,
        propsMapping,
    }

    if(typeof repeater === "object"){
        repeaterProps = { ...repeaterProps, ...repeater };
    }

    repeaterProps = { ...repeaterProps, ...commonProps };
    // END Repeater props

    /*typeof value === "string" ? value : JSON.stringify(value)*/
    return (
        <div className="meta-field">
            { fieldType === "repeater" &&
                <RepeaterField
                    {...repeaterProps}
                />
            }
            { fieldType === "group" &&
                <FieldsGroup
                    fields={fields}
                    {...commonProps}
                />
            }
            { fieldType === "single" &&
                <SingleField
                    {...singleProps}
                />
            }

            { fieldData.depth === 0 && // We don't generate hidden input for inner fields as the only one we care about is the main one
                <input ref={valueInputRef} type="hidden" name={name} value={JSON.stringify(value)} data-rb-main-value/>
            }
        </div>
    );
}
