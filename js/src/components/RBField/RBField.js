import FieldsGroup from 'COMPONENTS/FieldsGroup';
import SingleField from 'COMPONENTS/SingleField';
import RepeaterField from 'COMPONENTS/RepeaterField';

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
    const isSingle = component || (fields?.length === 1 && fields[0].component);
    let fieldType = "single";
    let value = passedValue;

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
        depth: parent ? parent.depth + 1 : 1,
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
                    // TODO: components prop mapping? not all components manages onChange the same way
                />
            }
            <input type="hidden" name={name} value={JSON.stringify(value)}/>
        </div>
    );
}



/*
Single
    Value: singleVal ||  groupVal ||  repeaterVal
Group (Single*)
    Value: { valueOne: singleVal, valueTwo: groupVal, valueThree: repeaterVal }
Repeater
    Value: (singleVal || groupVal || repeaterVal)[]

Repeater (Group*||Single*||Repeater*)

*/

/*
RBField({
    name: "header",
    repeater: false, // false,true, or json that accepts title and description (mayble collapsible options too)
    fields: [
        {
            name: "title",
            repeater: false,
            component: TextInput, // If the component field exists, it doesn't allow the fields field (either single or repeater)
            default: "This is the header title!",
        },
    ],
})

RBField({
    name: "metaName",
    title: "The input title",
    description: "This input does this thing.",
    repeater: false,
    component: TextInput, // If the component field exists, it doesn't allow the fields field (either single or repeater)
    default: "This is the header title!",
})


*/
