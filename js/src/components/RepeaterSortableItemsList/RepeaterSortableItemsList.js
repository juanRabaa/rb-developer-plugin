import { SortableRepeaterFieldItem } from "COMPONENTS/RepeaterFieldItem";
import {
  DndContext,
  closestCenter,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import {
  arrayMove,
  SortableContext,
  sortableKeyboardCoordinates,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import {
  restrictToVerticalAxis,
  restrictToWindowEdges,
} from '@dnd-kit/modifiers';

export default function RepeaterSortableItemsList(props){
    const {
        value,
        items,
        handleDragEnd: passedHandleDragEnd,
        handleDragStart,
     } = props;
    const sensors = useSensors(
        useSensor(PointerSensor, {
            // Require the mouse to move by 10 pixels before activating
            activationConstraint: {
                distance: 10,
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }));

    function handleDragEnd({ event }) {
        const {
            active,
            over
        } = event;
        let newValue;
        let changed = active.id !== over.id;

        if (changed) {
            const oldIndex = items.findIndex( item => item.id === active.id);
            const newIndex = items.findIndex( item => item.id === over.id);
            newValue = arrayMove(value, oldIndex, newIndex);
        }

        passedHandleDragEnd({
            event,
            items,
            changed,
            value: newValue,
        });
    }

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={(event) => handleDragStart({ event, items })}
            onDragEnd={(event) => handleDragEnd({ event, items })}
            modifiers={[restrictToVerticalAxis]}
        >
            <SortableContext
              items={items}
              strategy={verticalListSortingStrategy}
            >
                {items.map(({id, itemProps}) =>
                    <SortableRepeaterFieldItem
                        key={id}
                        id={id}
                        itemProps={itemProps}
                    />
                )}
            </SortableContext>
        </DndContext>
    );
}
