const { useState } = wp.element;
import { Icon } from '@wordpress/components';
import styles from './styles.module.scss';
import RepeaterTabs from "COMPONENTS/RepeaterTabs";
import RepeaterList from "COMPONENTS/RepeaterList";

// TODO: Should collapsible options be inside one general key `collapse`?
// TODO: Allow to define field as "unique", not allowing multiple items to have the same value in it
export default function RepeaterField(props){
    const {
        name,
        value,
        label,
        description,
        onChange,
        childFieldProps,
        dynamicTitle,
        collapse,
        collapseOpen = true,
        accordion,
        layout = "list",
        sortable,
        max,
        labels: passedLabels = {},
        ...passOnProps
    } = props;

    const labels = {
        maxReached: "Max amount of items reached",
        empty: "Start adding items!",
        itemTitle: "Item %n",
        ...passedLabels,
    };
    const currentAmount = value?.length ?? 0;
    const maxReached = typeof max === "number" && max >= 1 && currentAmount >= max;
    const isEmpty = !currentAmount;
    const [forceRefreshTime, setForceRefreshTimes] = useState(0); // TODO: This is used to change the keys of each repeater item when the order or amount of items change. It is a hacky way of doing this. I should see if there is a better way.

    console.log(`rep__${childFieldProps.parent.depth}`, 'Repeater value', value);

    const forceRefresh = () => {
        setForceRefreshTimes(forceRefreshTime + 1);
    };

    const handleItemChange = ({ index: itemIndex, value: itemValue, fieldType  }) => {
        // Updates old repeater value with the item new value
        console.log(`rep__${childFieldProps.parent.depth}__itemChange__${itemIndex}`, {
            value: itemValue,
            fieldType,
        });
        const newValue = value?.length ? [...value] : [];
        newValue[itemIndex] = fieldType === "group" ? {...itemValue} : itemValue;
        onChange({ value: newValue });
    };

    const removeItem = ({ itemIndex = -1 }) => {
        const newValue = value?.length ? [...value] : [];
        newValue.splice(itemIndex, 1);
        onChange({ value: newValue });
        forceRefresh();
    };

    const addItem = () => {
        const newValue = value?.length ? [...value] : [];
        newValue.push(null);
        onChange({ value: newValue });
    };

    const getItemTitle = ({value: itemValue, index }) => {
        let itemTitle = labels.itemTitle.replaceAll("%n", index + 1);

        if(dynamicTitle && itemValue?.[dynamicTitle]){
            const fieldValue = itemValue[dynamicTitle].trim();
            if( fieldValue )
                itemTitle = fieldValue;
        }

        return itemTitle;
    }

    const repeaterItems = () => {
        const items = [];
        for(let itemIndex = 0; itemIndex < currentAmount; itemIndex++){
            const itemName = `${name}__${itemIndex}`;
            const itemValue = value?.[itemIndex];

            console.log(`rep__${childFieldProps.parent.depth}__itemRender__${itemIndex}`, itemValue);

            const itemProps = {
                name: itemName,
                value: itemValue,
                index: itemIndex,
                onChange: handleItemChange,
                removeItem: () => removeItem({itemIndex}),
                title: getItemTitle({ value: itemValue, index: itemIndex }),
                fieldProps: {
                    ...passOnProps,
                    ...childFieldProps
                },
            };

            let itemData = {
                id: `${itemName}${forceRefreshTime}`,
                itemProps,
            };

            items.push(itemData);
        }

        return items;
    }

    function getEmptyMessage(){
        return (
            <>
                <div className="empty-repeater-message">
                    <p>{labels.empty}</p>
                </div>
            </>
        );
    }

    function getRepeaterItemsContainer(){
        const itemsComponentProps = {
            items: repeaterItems(),
            value,
            addItem,
            maxReached,
            isEmpty,
            sortable,
            getEmptyMessage,
            forceRefresh,
            onChange,
            labels,
        };

        if(layout === "tabs"){
            return (
                <RepeaterTabs {...itemsComponentProps} />
            );
        }

        return (
            <RepeaterList
                {...itemsComponentProps}
                collapse={collapse}
                collapseOpen={collapseOpen}
                accordion={accordion}
            />
        );
    }

    return (
        <div className={`meta-repeater-field`}>
            { label && <label>{label}</label> }
            { description && <p>{description}</p> }
            {getRepeaterItemsContainer()}
        </div>
    );
}
