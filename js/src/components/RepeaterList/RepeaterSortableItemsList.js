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
import RepeaterFieldItem from "./RepeaterFieldItem";

export default function RepeaterSortableItemsList(props){
    const {
        value,
        items,
        handleDragEnd: passedHandleDragEnd,
        handleDragStart,
        sortable,
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

    const ItemsWrapper = ({children}) => {
        if(!sortable)
            return <>{children}</>;

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
                    {children}
                </SortableContext>
            </DndContext>
        );
    };


    return (
        <ItemsWrapper>
            {items.map(({id, itemProps}) =>
                <RepeaterFieldItem
                    {...itemProps}
                    key={id}
                    id={id}
                />
            )}
        </ItemsWrapper>
    );
}
