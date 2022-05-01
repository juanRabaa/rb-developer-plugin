import { Icon } from '@wordpress/components';
import {useSortable} from '@dnd-kit/sortable';
import {CSS} from '@dnd-kit/utilities';

export function Tab({
    tab,
    active,
    onClick,
    onClose
}) {
    const {
        title,
        id
    } = tab;

    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
    } = useSortable({ id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition: transition, //, border-radius 0.4s, background-color 0.4s, box-shadow 0.4s
    };

    const containerProps = {
        ref: setNodeRef,
        style,
    };

    const headerProps = {
        ...attributes,
        ...listeners,
    };

    const activeClass = active ? "active" : "";

  return (
      <div {...containerProps} {...headerProps} className={`tab tab-${id} ${activeClass}`} key={id} onClick={onClick}>
          <p className="tab-title">{title}</p>
          <div className="close-btn" onClick={(event) => {
              event.stopPropagation();
              onClose();
          }}>
            <Icon icon="no-alt"/>
          </div>
      </div>
  );
}
