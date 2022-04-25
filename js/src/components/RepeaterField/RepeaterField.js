const { useState } = wp.element;
import { Icon } from '@wordpress/components';
import styles from './styles.module.scss';
import RepeaterFieldItem from "COMPONENTS/RepeaterFieldItem";
import RepeaterSortableItemsList from 'COMPONENTS/RepeaterSortableItemsList';

// TODO: Sortable
// TODO: Option to generate the items in tabs instead of list items
// TODO: Should collapsible options be inside one general key `collapse`?
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
    const [forceCollapseOpen, setForceCollapseOpen] = useState(null);
    const [accordionIndex, setAccordionIndex] = useState(collapseOpen ? 0 : null);
    console.log(`rep__${childFieldProps.parent.depth}`, 'Repeater value', value);

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
        setForceRefreshTimes(forceRefreshTime + 1);
        if(accordion){
            if(itemIndex < accordionIndex)
                setAccordionIndex(accordionIndex - 1);
            else if(itemIndex === accordionIndex)
                setAccordionIndex(null);
        }
    };

    const addItem = () => {
        const newValue = value?.length ? [...value] : [];
        newValue.push(null);
        onChange({ value: newValue });
    };

    const repeaterItems = () => {
        const items = [];
        for(let itemIndex = 0; itemIndex < currentAmount; itemIndex++){
            const itemName = `${name}__${itemIndex}`;
            const itemValue = value?.[itemIndex];
            let open;

            if(forceCollapseOpen !== null)
                open = forceCollapseOpen;
            else if(accordion)
                open = itemIndex === accordionIndex;
            console.log(`rep__${childFieldProps.parent.depth}__itemRender__${itemIndex}`, itemValue);

            const itemProps = {
                name: itemName,
                value: itemValue,
                index: itemIndex,
                onChange: handleItemChange,
                removeItem,
                dynamicTitle,
                baseTitle: labels.itemTitle,
                fieldProps: {
                    ...passOnProps,
                    ...childFieldProps
                },
                onCollapseTriggerClick: () => {
                    if(accordion)
                        setAccordionIndex(itemIndex === accordionIndex ? null : itemIndex);
                },
                collapse,
                open,
                initialOpen: collapseOpen,
            };

            items.push({
                id: `${itemName}${forceRefreshTime}`,
                itemProps,
            });
        }

        if( sortable ){
            return (
                <RepeaterSortableItemsList
                    value = {value}
                    items = {items}
                    handleDragStart = { () => {
                        setForceCollapseOpen(false);
                    }}
                    handleDragEnd = { ({value, changed}) => {
                        setForceCollapseOpen(null);
                        if(changed){
                            setForceRefreshTimes(forceRefreshTime + 1);
                            onChange({ value });
                        }
                    } }
                />
            );
        }

        return items.map( ({id, itemProps}) => <RepeaterFieldItem key={id} {...itemProps}/> );
    }



    return (
        <div className={`${styles.repeaterField} meta-repeater-field ${accordion ? styles.accordion : ''}`}>
            { label && <label>{label}</label> }
            { description && <p>{description}</p> }
            <div className={styles.repeaterItems}>
                {repeaterItems()}
            </div>
            {isEmpty && labels.empty &&
            <div className="empty-repeater-message">
                <p>{labels.empty}</p>
            </div>
            }
            { !maxReached &&
            <div className={`${styles.addButtonContainer} add-button-container`}>
                <Icon icon="plus" className={styles.addBtn} onClick={addItem} />
            </div>
            }
            { maxReached &&
            <div className="max-reached-info">
                <p>{labels.maxReached}</p>
            </div>
            }
        </div>
    );
}
