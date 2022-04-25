import RBField from 'COMPONENTS/RBField';

export default function FieldsGroup({ name, value, label, description, fields, childFieldProps, onChange }){

    const handleFieldChange = ({ name: fieldName, value: fieldValue  }) => {
        // Updates old value with the changed field
        console.log(`group__fieldChange__${fieldName}`, fieldValue);
        onChange({
            value: {
                ...value,
                [fieldName]: fieldValue,
            },
        });
    };

    const groupFields = fields.map( (field) => {
        // const fieldName = `${name}__${field.name}`;
        const fieldValue = value ? value?.[field.name] : null;
        console.log(`group__fieldRender__${field.name}`, fieldValue);
        return (
            <div className="group-field" key={`${name}__${field.name}`}>
                <RBField {...field} name={field.name} {...childFieldProps} value={fieldValue} onChange={({ value }) => handleFieldChange({
                    name: field.name,
                    value: value,
                })}/>
            </div>
        );
    });

    return (
        <div className="meta-field-group">
            { label && <label>{label}</label> }
            { description && <p>{description}</p> }
            <div className="group-fields">
                {groupFields}
            </div>
        </div>
    )

}
