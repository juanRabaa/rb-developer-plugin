import React from "react";
import { render } from "react-dom";
import RBFinalField from 'COMPONENTS/RBFinalField';
import parsePHPFieldData from "HELPERS/parsePHPFieldData";
import $ from 'jquery';
// const apiFetch = wp.apiFetch;

export default function renderField({ $el, value, fieldConfig}){
    if(!$el.length)
        return;

    const parsedFieldConfig = parsePHPFieldData(fieldConfig);
    // let metaValue = JSON.parse($el.attr("data-value"));
    render(<RBFinalField {...parsedFieldConfig } value={value} />, $el[0]);
}
