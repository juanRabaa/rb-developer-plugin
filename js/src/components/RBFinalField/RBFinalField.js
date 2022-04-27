import React, {useState} from "react";
import RBField from 'COMPONENTS/RBField';

/**
*   Automanaged RBField.
*/
export default function RBFinalField(props){
    const [value, setValue] = useState(props.value);

    return (
        <RBField {...props} value = {value} onChange = { ({value}) => setValue(value) }/>
    );
}
