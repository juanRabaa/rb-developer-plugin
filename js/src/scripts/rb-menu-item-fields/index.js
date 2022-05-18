import { render, unmountComponentAtNode } from "react-dom";
import React from "react";
import RBFinalField from 'COMPONENTS/RBFinalField';
import parsePHPFieldData from "HELPERS/parsePHPFieldData";
import $ from 'jquery';

const registeredFields = RBMenuItemFields?.fields;

function renderFieldsInside($el){
    if(!registeredFields)
        return;

    Object.keys(registeredFields).forEach((metaKey) => {
        const fieldConfig = parsePHPFieldData(registeredFields[metaKey].field);
        const $rowPlaceholder = $el.find(`[data-field="rb-menu-item-field__${metaKey}"]`);
        if(!$rowPlaceholder.length)
            return;

        $rowPlaceholder.each( function(){
            let metaValue = JSON.parse($(this).attr("data-value"));
            let itemID = $(this).data("itemid");
            // Add item ID to the input name to distinguish the meta value in the save proccesse
            const itemFieldConfig = {
                ...fieldConfig,
                name: `${fieldConfig.name}[${itemID}]`,
            };

            render(<RBFinalField {...itemFieldConfig } value={metaValue} />, $(this)[0]);
        });
    } );
}

/**
*   Replaces the placeholder of fields for the REACT component that manages the
*   meta value.
*/
$(document).ready( function(){
    console.log("registeredFields", registeredFields);
    renderFieldsInside($(document));
});


// RENDER FIELDS ON NEW ITEMS
$( document ).on( "menu-item-added", function(event, $menuItems){
    const registeredFields = RBMenuItemFields?.fields;

    if(registeredFields && !$menuItems?.length)
        return;

    renderFieldsInside($menuItems);
} );

// REMOVE FIELDS ON ITEMS TO BE REMOVED
$( document ).on( "menu-removing-item", function(event, $menuItems){
    if(!$menuItems?.length)
        return;

    $menuItems.find(`[data-field^="rb-menu-item-field__"]`).each( function(){
        unmountComponentAtNode($(this)[0]);
    })
});

/**
*   Thankfully, this hack didnt had to be used, as the wpNavMenu api actually
*   triggers some events that you can listen to to trigger the fields renders/unmounts
*   I'll leave it here just in case.
*/
// const event = new Event('wpNavMenuItemsAddedToMenu');
// const oldAddItemToMenu = wpNavMenu.addItemToMenu;
// wpNavMenu.addItemToMenu = function(menuItem, processMethod, callback){
//     oldAddItemToMenu(menuItem, processMethod, function(){
//         callback();
//         event.itemsAdded = [];
//         Object.keys(menuItem).forEach((itemKey, i) => {
//             event.itemsAdded.push(menuItem[itemKey]);
//         });
//         console.log("itemsAdded", event.itemsAdded);
//         document.dispatchEvent(event);
//     });
// };
