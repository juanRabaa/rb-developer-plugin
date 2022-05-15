import React, {useState, useEffect} from "react";
import RBField from 'COMPONENTS/RBField';

/**
*   Automanaged RBField.
*/
export default function RBFinalField({ value: initialValue, onRender, onChange: passedOnChange, ...fieldProps}){
    const [value, setValue] = useState(initialValue);

    // REVIEW: This is not right... should check another way to update the value
    useEffect( () => {
        if(onRender){
            onRender({
                value,
                setValue,
            });
        }
    }, [onRender]);

    const onChange = ({value}) => {
        setValue(value);
        if(passedOnChange)
            passedOnChange({value});
    }

    return (
        <RBField {...fieldProps} value = {value} onChange = { (data) => onChange(data) }/>
    );
}
