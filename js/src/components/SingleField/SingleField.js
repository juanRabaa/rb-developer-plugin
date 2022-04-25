import getComponent from './components';

// TODO: Documentation (rbFieldsComponentsList filter to modify components list)
/**
*   @param {JSX|String} component                                               Component or name of a component from the default library
*/
export default function SingleField({ value: realValue, defaultValue, label, description, placeholder, onChange, component, componentProps: passedComponentProps, propsMapping: passedPropsMapping }){
    const value = realValue !== undefined ? realValue : defaultValue;
    let Component;
    let propsMapping = {
        value: "value",
        placeholder: "placeholder",
        onChange: "onChange",
        filterOnChange: null,
    };
    let componentProps = {};

    // Props needs to be maped as not all components uses the same prop names for the
    // necessary values and callbacks that the field has to pass to them.
    const setComponentAndProps = () => {
        if( typeof component === "string"){
            const componentData = getComponent({ name: component });

            if(componentData){
                Component = componentData.component;
                if(componentData.propsMapping)
                    propsMapping = {...propsMapping, ...componentData.propsMapping};
            }
            else if(wp.components[component]){
                Component = wp.components[component];
            }
        }
        else{
            Component = component;
        }

        if(passedPropsMapping)
            propsMapping = {...propsMapping, ...passedPropsMapping};

        componentProps = {
            ...passedComponentProps,
            [propsMapping.value]: value,
            [propsMapping.placeholder]: placeholder,
            [propsMapping.onChange]: function(newValue){
                // Not all components send the same params to the onChange callback. By default,
                // the first param is expected to contain the new value, but for other cases, the `filterOnChange`
                // callback allows to narrow down the data sent by the component to the value we need.
                onChange({
                    value: propsMapping.filterOnChange ? propsMapping.filterOnChange(...arguments) : newValue,
                });
            },
        };
    };

    setComponentAndProps();

    return (
        <div className="single-field">
            {Component &&
                <>
                    { label && <label>{label}</label> }
                    { description && <p>{description}</p> }
                    <Component {...componentProps}/>
                </>
            }
            {!Component &&
                <>
                    <h5>Component not found <span>:(</span></h5>
                </>
            }
        </div>
    );
}
