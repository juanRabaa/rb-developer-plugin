import React from "react";
const { useSelect } = wp.data;
const { Spinner } = wp.components;

export default function MediaUploadCheck(props) {
    const {
        fallback = null,
        children,
    } = props;

    const {
        checkingPermissions,
        hasUploadPermissions,
    } = useSelect(select => {
        const core = select("core");
        return {
            hasUploadPermissions: core.canUser("read", "media"),
            checkingPermissions: !core.hasFinishedResolution("canUser", [ "read", "media"]),
        };
    });

    return <>
        {checkingPermissions && <Spinner/>}
        {(!checkingPermissions && hasUploadPermissions) ? children : fallback}
    </>;
}
