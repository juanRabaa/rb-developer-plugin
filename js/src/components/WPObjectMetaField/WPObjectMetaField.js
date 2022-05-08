import { useEffect, useState, useRef } from "react";
import RBField from 'COMPONENTS/RBField';
const { camelCase } = lodash;
const { useSelect, useDispatch } = wp.data;
const { Spinner } = wp.components;
const apiFetch = wp.apiFetch;
/**
*/
export default function WPObjectMetaField(props){
    const {
        name,
        objectSubtype, // postType - taxonomy
        subtypeKind,
        objectID,
        ...metaFieldProps
    } = props;
    const parsedObjectSubtype = camelCase(objectSubtype);
    const editionTimes = useRef(0);
    const [saveHappened, setSaveHappened] = useState(false);
    const { objectData, fetchingObject, saving } = useSelect( function(select){
        const core = select("core");
        return {
            objectData: core.getEditedEntityRecord(parsedObjectSubtype, subtypeKind, objectID),
            fetchingObject: !core.hasFinishedResolution("getEditedEntityRecord", [ parsedObjectSubtype, subtypeKind, objectID ]),
            saving: saveHappened && !core.hasFinishedResolution("saveEditedEntityRecord", [ parsedObjectSubtype, subtypeKind, objectID ]),
        }
    });
    const { editEntityRecord, saveEditedEntityRecord } = useDispatch("core");

    // REVIEW: Should a save button be added? how problematic can it be to make the
    // save of the meta every 3000 ms after last input?
    useEffect( () => {
        if(editionTimes.current > 0){
            const timeout = setTimeout( () => {
                setSaveHappened(true);
                saveEditedEntityRecord(parsedObjectSubtype, subtypeKind, objectID);
            }, 3000);
            return () => {
                clearTimeout(timeout);
            };
        }
        editionTimes.current++;
    }, [objectData]);

    function getMetaValue(){
        return objectData?.meta?.[name] ?? undefined;
    }

    function updateObject({ value: newMetaValue }){
        editEntityRecord(
            parsedObjectSubtype,
            subtypeKind,
            objectID,
            {
                meta: {
                    [name]: newMetaValue,
                },
            },
        );
    }

    return (
        <>
            {fetchingObject && <Spinner/>}
            {!fetchingObject &&
                <RBField
                    {...props}
                    value={getMetaValue()}
                    onChange = {updateObject}
                />
            }
        </>
    )
}
