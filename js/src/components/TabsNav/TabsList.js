import { Fragment } from "react";
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
  horizontalListSortingStrategy,
} from '@dnd-kit/sortable';
import {
  restrictToHorizontalAxis,
  restrictToWindowEdges,
} from '@dnd-kit/modifiers';
import { Tab } from "./Tab";

export default function TabsList(props){
    const {
        tabs,
        current,
        sortable,
        handleDragEnd: passedHandleDragEnd,
        handleDragStart,
        onTabChange,
        onCloseTab,
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
        let changed = active.id !== over.id;
        let oldIndex, newIndex;

        if (changed) {
            oldIndex = tabs.findIndex( item => item.id === active.id);
            newIndex = tabs.findIndex( item => item.id === over.id);
            onTabChange({ tab: tabs?.[newIndex], index: newIndex });
        }

        passedHandleDragEnd({
            event,
            tabs,
            changed,
            newIndex,
            oldIndex,
            arrayMove,
        });
    }

    // TODO: The dragged tab should be activated on drag start, but doing so generates
    // a bug in the sortable logic, i guess because it triggers a re-render
    const onDragStart = ({ event }) => {
        // const tabIndex = event.active.data.current.sortable.index;
        // onTabChange({ tab: tabs?.[tabIndex], index: tabIndex });
        // console.log("event", event);
    };

    const TabsWrapper = ({children}) => {
        if(!sortable)
            return <>{children}</>;

        return (
            <DndContext
                sensors={sensors}
                collisionDetection={closestCenter}
                onDragEnd={(event) => handleDragEnd({ event })}
                onDragStart={(event) => onDragStart({ event })}
                modifiers={[restrictToHorizontalAxis]}
            >
                <SortableContext
                  items={tabs}
                  strategy={horizontalListSortingStrategy}
                >
                    {children}
                </SortableContext>
            </DndContext>
        );
    };

    // onDragStart={(event) => handleDragStart({ event, tabs })}
    return (
        <TabsWrapper>
            {tabs.map((tab, mapInd) =>
                <Tab
                    key={tab.id}
                    id={tab.id}
                    tab={tab}
                    active={mapInd === current}
                    onClick={() => onTabChange({ tab, index: mapInd })}
                    onClose={() => onCloseTab({ tab, index: mapInd })}
                />
            )}
        </TabsWrapper>
    );
}
