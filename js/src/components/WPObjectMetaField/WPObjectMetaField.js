import { useEffect, useState, useRef } from "react";
import RBField from 'COMPONENTS/RBField';
const { camelCase } = lodash;
const { useSelect, useDispatch } = wp.data;
const { Spinner } = wp.components;
const apiFetch = wp.apiFetch;

function useEditedEntityAutosave({
    delay,
    objectSubtype,
    subtypeKind,
    objectID
}){
    const [editionTimes, setEditionTimes] = useState(0);
    const [savingTimes, setSavingTimes] = useState(0);
    const [listeningToChange, setListeningToChange] = useState(false);
    const { saveEditedEntityRecord } = useDispatch("core");
    const { objectData, fetchingObject } = useSelect( function(select){
        const core = select("core");
        return {
            objectData: core.getEditedEntityRecord(objectSubtype, subtypeKind, objectID),
            fetchingObject: !core.hasFinishedResolution("getEditedEntityRecord", [ objectSubtype, subtypeKind, objectID ]),
            // saving: core.isSavingEntityRecord(objectSubtype, subtypeKind, objectID),
        }
    });

    useEffect( () => {
        if(editionTimes > 0){
            setListeningToChange(true);
            console.log("DELAY");
            const timeout = setTimeout( () => {
                console.log("SAVING");
                setListeningToChange(false);
                saveEditedEntityRecord(objectSubtype, subtypeKind, objectID);
            }, delay);
            return () => {
                console.log("Save cancelled");
                setListeningToChange(false);
                clearTimeout(timeout);
            };
        }
    }, [editionTimes]);

    // useEffect( () => {
    //     console.log("Object data changed");
    //     setEditionTimes(0);
    // }, [objectData]);

    return {
        objectData,
        fetchingObject,
        triggerSave(){
            console.log("Trigger save");
            setEditionTimes(editionTimes + 1);
        },
    }
}

/**
*/
export default function WPObjectMetaField(props){
    const {
        name,
        objectSubtype, // postType - taxonomy
        subtypeKind,
        objectID,
        onChange,
        ...metaFieldProps
    } = props;
    const parsedObjectSubtype = camelCase(objectSubtype);
    const { triggerSave, objectData, fetchingObject } = useEditedEntityAutosave({
        delay: 3000,
        objectSubtype: parsedObjectSubtype,
        subtypeKind,
        objectID,
    });
    const { editEntityRecord } = useDispatch("core");

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
        triggerSave();
        if(onChange)
            onChange({ value: newMetaValue });
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
