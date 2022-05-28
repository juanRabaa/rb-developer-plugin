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
*   @property {object} quickEditChanges                                         Keeps track of the custom metas that had been changed
*                                                                               in the quick edit.
*/
let quickEditChanges;

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

    // ON SAVE (old row removed and replace with new one)
    onElementRemoved($objectRow[0], function(){
        dispatchObjectEdition({ objectID });
        // Removed field components from old post row that was removed.
        $(".rb-field-col-placeholder", $objectRow).each( function(index){
            unmountComponentAtNode(this);
        });
        // Render fields in new post row
        renderColumnsFields({ parent: getObjectRow({objectID}) });
    });

    // ON QUICK EDIT BOX CLOSE
    onElementRemoved($editRow[0], function(){
        unmountComponents();
    });
};
