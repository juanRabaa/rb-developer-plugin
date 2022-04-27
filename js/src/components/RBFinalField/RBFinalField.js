import React, {useState, useEffect} from "react";
import RBField from 'COMPONENTS/RBField';

/**
*   Automanaged RBField.
*/
export default function RBFinalField({ value: initialValue, onRender, ...fieldProps}){
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

    return (
        <RBField {...fieldProps} value = {value} onChange = { ({value}) => setValue(value) }/>
    );
}
