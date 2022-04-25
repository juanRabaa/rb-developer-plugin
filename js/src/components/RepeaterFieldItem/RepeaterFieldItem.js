const { useState, useEffect } = wp.element;
import RBField from '../RBField';
import { Icon } from '@wordpress/components';
import styles from '../RepeaterField/styles.module.scss';
import {useSortable} from '@dnd-kit/sortable';
import {CSS} from '@dnd-kit/utilities';

export default function RepeaterFieldItem(props){
    const {
        name,
        value,
        index,
        onChange,
        removeItem,
        dynamicTitle,
        baseTitle = "Item %n",
        fieldProps,
        collapse,
        onCollapseTriggerClick,
        open,
        initialOpen,
        containerProps = {},
        headerProps = {},
    } = props;

    const [collapseOpen, setCollapseOpen] = useState(initialOpen);

    const getItemTitle = () => {
        let itemTitle = baseTitle.replaceAll("%n", index + 1);

        if(dynamicTitle && value?.[dynamicTitle]){
            const fieldValue = value[dynamicTitle].trim();
            if( fieldValue )
                itemTitle = fieldValue;
        }

        return itemTitle;
    }

    const collapseTriggerClicked = () => {
        if(!collapse)
            return;
        if(open === undefined)
            setCollapseOpen(!collapseOpen);
        onCollapseTriggerClick();
    };

    useEffect(() => {
        if(open !== undefined)
            setCollapseOpen(open);
    }, [open]);

    const itemTitle = getItemTitle();

    return (
        <div {...containerProps} className={`${styles.repeaterItem} repeater-item`}>
            <div {...headerProps } className={`${styles.itemHeader} ${collapse ? styles.collapse : ''}`} onClick={ collapseTriggerClicked }>
                { itemTitle &&
                    <b className={styles.itemTitle}>{ itemTitle }</b>
                }
                <div className={styles.actions}>
                    <Icon icon="trash" className={styles.trashIcon} onClick={(e) => {
                        e.stopPropagation();
                        removeItem({ itemIndex: index });
                    }} />
                </div>
            </div>
            { (!collapse || collapseOpen) &&
                <div className={styles.itemContent}>
                    <RBField {...fieldProps} name={name} value={value} onChange={({ value, fieldType }) => onChange({
                        index,
                        value,
                        fieldType,
                    })}/>
                </div>
            }

        </div>
    );
}

export function SortableRepeaterFieldItem({ id, itemProps }) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
  } = useSortable({id});

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

  const containerProps = {
      ref: setNodeRef,
      style,
  };

  const headerProps = {
      ...attributes,
      ...listeners,
  };

  return (
      <RepeaterFieldItem containerProps={containerProps} headerProps={headerProps} {...itemProps}/>
  );
}
