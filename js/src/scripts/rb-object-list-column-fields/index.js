import $ from 'jquery';
import { renderColumnsFields } from './helpers';
import "./quickEdit";

/**
*   Replaces the placeholder of fields for the REACT component that manages the
*   meta value.
*/
async function renderFields(){
    $(document).ready( function(){
        renderColumnsFields({ parent: document });
    });
}

renderFields();
