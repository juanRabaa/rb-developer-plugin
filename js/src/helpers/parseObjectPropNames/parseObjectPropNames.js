/**
*   Recursively modifies an object prop keys for the ones in the mapping object.
*/
export default function parseObjectPropNames(source, mapping){
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
