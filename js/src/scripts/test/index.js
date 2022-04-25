const [metaValue, setMetaValue] = useState();

const metaField = {
    name: "meta_test",
    label: "Group",
    description: "",
    repeater: true,
    value: metaValue,
    fields: [
        {
            name: "titles",
            label: "Titles",
            description: "This input does this thing.",
            repeater: false,
            component: "text",
        },
        {
            name: "attachment",
            label: "Attachments",
            repeater: false,
            component: "attachments",
            componentProps: {
                gallery: true,
            },
        },
    ],
    onChange: (data) => {
        console.log('Meta Control Value', data.value);
        setMetaValue(data.value);
    },
};

const repeaterMetaField = {
    name: "meta_test",
    label: "The input title",
    description: "This input does this thing.",
    repeater: true,
    component: "text", // If the component field exists, it doesn't allow the fields field (either single or repeater)
    value: metaValue,
    onChange: (data) => {
        console.log(data);
        setMetaValue(data.value);
    },
};
