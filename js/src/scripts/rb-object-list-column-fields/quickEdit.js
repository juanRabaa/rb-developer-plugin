import { render, unmountComponentAtNode } from "react-dom";
import { getWPObjectsListFields, renderColumnsFields, getObjectRowFieldValue, getObjectSingleKey, getObjectRow } from './helpers';
import { onElementRemoved } from "HELPERS/helpers";
import RBFinalField from 'COMPONENTS/RBFinalField';
import $ from 'jquery';
const { camelCase } = lodash;

// we create a copy of the WP inline edit post function
const inlineEditObject = window.inlineEditPost ?? window.inlineEditTax;
const $wp_inline_edit = inlineEditObject.edit;

/**
*   @property {HTML[]} lastPlaceholders                                         The latest node where components where mounted in
*                                                                               the quick edit box.
*/
const lastPlaceholders = [];

/**
*   @property {bool} closedBySaving                                             Indicates if the user closed the quick edit saving
*                                                                               its changes.
*/
let closedBySaving;

/**
*   @property {object} quickEditChanges                                         Keeps track of the custom metas that had been changed
*                                                                               in the quick edit.
*/
let quickEditChanges;

/**
*   The user can close the quick edit row by editer saving the edit or canceling it.
*   There is no way to detect which way the user went, since there are no event triggers.
*   The only way to know if the user actually saved the changes is to listen to the ajaxComplete
*   that gets triggered by the save.
*   We need to know if the user saved to dispatch the change to the wp.data.dispatch("core").editEntityRecord
*   since the columns fields feed from the editedRecord store.
*/
function checkIfClosedBySaving(event, request, settings){
    if(settings?.data){
        const searchParams = new URLSearchParams(settings.data);
        const action = searchParams.get("action");
        if(action === "inline-save" || action === "inline-save-tax"){
            closedBySaving = true;
            $(document).off("ajaxComplete", checkIfClosedBySaving);
        }
    }
}

/**
*   Unmounts all the components from the quick edit.
*/
function unmountComponents(){
    lastPlaceholders.forEach(unmountComponentAtNode);
    lastPlaceholders.length = 0; //empty array once unmounted
}

/**
*   Dispatches the changes made in the quick edit to update the components that
*   uses getEditedEntityRecord. Generally used to keep the column fields values
*   updated with the changes made in the quick edit box.
*/
function dispatchObjectEdition({ objectID }){
    const metasChanged = Object.keys(quickEditChanges);
    const changes = {
        meta: {},
    };

    if(metasChanged.length === 0)
        return;

    metasChanged.forEach(( metakey, i) => {
        const newMetaValue = quickEditChanges[metakey];
        changes.meta[metakey] = newMetaValue;
    });

    wp.data.dispatch("core").editEntityRecord(
        camelCase(RBObjectsList.objectSubtype),
        RBObjectsList.subtypeKind,
        objectID,
        changes,
    );
}

/**
*   Extend the worpdress inlineEditPost.edit method that gets triggered when
*   opening the quick edit box
*/
inlineEditObject.edit = function( editBtn ) {
    // "call" the original WP edit function
    $wp_inline_edit.apply( this, arguments );

    // get the post ID
    let objectID = 0;
    if ( typeof( editBtn ) == 'object' ) {
        objectID = parseInt( this.getId( editBtn ) );
    }

    if( !objectID || objectID <= 0 )
        return;

    // define the edit row
    const $editRow = $( `#edit-${objectID}` );
    const $objectRow = $( `#${getObjectSingleKey()}-${objectID}` );

    // Initialize variables
    closedBySaving = false;
    quickEditChanges = {};

    // Render the fields on the placeholders inside $editRow
    $(".rb-quick-edit-field-placeholder", $editRow).each( async function(){
        lastPlaceholders.push(this);
        const $placeholder = $(this);
        const metakey = $placeholder.data("metakey");
        const metaValue = JSON.parse( getObjectRowFieldValue({ objectID, metakey }));
        const parsedFieldsConfig = await getWPObjectsListFields();
        const fieldConfig = parsedFieldsConfig[metakey];

        render(<RBFinalField {...fieldConfig } value={metaValue} onChange={
            ({value: newValue}) => {
                quickEditChanges[metakey] = newValue;
            }
        }/>, $placeholder[0]);
    });

    $(document).off("ajaxComplete", checkIfClosedBySaving);
    $(document).on("ajaxComplete", checkIfClosedBySaving);

    onElementRemoved($editRow[0], function(){
        unmountComponents();

        if(closedBySaving){
            dispatchObjectEdition({ objectID });
            // Removed field components from old post row that was removed.
            $(".rb-field-col-placeholder", $objectRow).each( function(index){
                unmountComponentAtNode(this);
            });
            // Render fields in new post row
            renderColumnsFields({ parent: getObjectRow({objectID}) });
        }
    });
};
