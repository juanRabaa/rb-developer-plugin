const { useState, useEffect } = wp.element;
import RBField from 'COMPONENTS/RBField';
import TabsNav from "COMPONENTS/TabsNav";

export default function RepeaterTabs({ items: passedItems, addItem, maxReached, isEmpty, getEmptyMessage, value, sortable, onChange }){
    const [currentTab, setCurrentTab] = useState(0);

    const items = passedItems ? passedItems.map( (itemData, itemIndex) => {
        return {
            ...itemData,
            title: itemData.itemProps.title,
            itemProps: {
                ...itemData.itemProps,
            },
        };
    }) : [];

    const onSort = ({event, changed, oldIndex, newIndex, arrayMove}) => {
        if(!changed)
            return;
        const newValue = arrayMove(value, oldIndex, newIndex);
        console.log("ON SORT", oldIndex, newIndex);
        onChange({ value: newValue });
    };

    return (
        <TabsNav
            current={currentTab}
            tabs={items}
            sortable={sortable}
            onSort={onSort}
            generateTabContent={ ({ index, tab }) => {
                if(isEmpty){
                    return getEmptyMessage();
                }

                if(!tab)
                    return null;

                const { fieldProps, name, value, onChange: itemOnChange } = tab.itemProps;

                return (
                    <RBField {...fieldProps} name={name} value={value} onChange={({ value, fieldType }) => itemOnChange({
                        index,
                        value,
                        fieldType,
                    })}/>
                );
            }}
            onTabChange={ ({index}) => {
                setCurrentTab(index);
            }}
            onCloseTab={ ({tab, index}) => {
                console.log('CLOSING', index, currentTab);
                if(currentTab !== 0 && index <= currentTab)
                    setCurrentTab(currentTab - 1);
                tab.itemProps.removeItem();
            } }
            onAddTab={addItem}
            additionDisabled={maxReached}
        />
    );
}
