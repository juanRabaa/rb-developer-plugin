const { useState } = wp.element;
const { Icon } = wp.components;
import RepeaterSortableItemsList from "./RepeaterSortableItemsList";
import RepeaterFieldItem from "./RepeaterFieldItem";
import styles from './styles.module.scss';

export default function RepeaterList({
    items: passedItems,
    value,
    labels,
    addItem,
    maxReached,
    isEmpty,
    collapse,
    sortable,
    accordion,
    collapseOpen,
    getEmptyMessage,
    forceRefresh,
    onChange
}){
    const [forceCollapseOpen, setForceCollapseOpen] = useState(null);
    const [accordionIndex, setAccordionIndex] = useState(collapseOpen ? 0 : null);

    const items = passedItems?.map( (itemData, itemIndex) => {
        let open;

        if(forceCollapseOpen !== null)
            open = forceCollapseOpen;
        else if(accordion)
            open = itemIndex === accordionIndex;

        return {
            ...itemData,
            itemProps: {
                ...itemData.itemProps,
                initialOpen: collapseOpen,
                collapse,
                open,
                onCollapseTriggerClick: () => {
                    if(accordion)
                        setAccordionIndex(itemIndex === accordionIndex ? null : itemIndex);
                },
                removeItem: function(data){
                    const { itemIndex = -1 } = data;
                    itemData.itemProps.removeItem(data);
                    if(accordion){
                        if(itemIndex < accordionIndex)
                            setAccordionIndex(accordionIndex - 1);
                        else if(itemIndex === accordionIndex)
                            setAccordionIndex(null);
                    }
                },
            },
        };
    });

    if(sortable){
        return (
            <div className={`${styles.repeaterList} ${accordion ? styles.accordion : ''}`}>
                <div className={styles.repeaterItems}>
                    <RepeaterSortableItemsList
                        value = {value}
                        items = {items}
                        handleDragStart = { () => {
                            setForceCollapseOpen(false);
                        }}
                        handleDragEnd = { ({value, changed}) => {
                            setForceCollapseOpen(null);
                            if(changed){
                                forceRefresh();
                                onChange({ value });
                            }
                        } }
                    />
                    {isEmpty && getEmptyMessage()}
                </div>
                { maxReached &&
                <div className="max-reached-info">
                    <p>{labels.maxReached}</p>
                </div>
                }
                { !maxReached &&
                <div className={`${styles.addButtonContainer} add-button-container`}>
                    <Icon icon="plus" className={styles.addBtn} onClick={addItem} />
                </div>
                }
            </div>
        );
    }

    return items.map( ({id, itemProps}) => <RepeaterFieldItem key={id} {...itemProps}/> );
}
