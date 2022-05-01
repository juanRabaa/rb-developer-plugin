import { Icon } from '@wordpress/components';
import TabsList from "./TabsList";
import styles from './styles.scss';

export default function TabsNav(props){
    const {
        current,
        tabs, // title, id
        generateTabContent,
        onTabChange,
        onCloseTab,
        onAddTab,
        sortable,
        onSort,
        additionDisabled,
    } = props;

    function currentTabContent(){
        return generateTabContent({ tab: tabs?.[current], index: current, tabs });
    }

    function tabChange(data){
        if(data.index === current)
            return;
        onTabChange(data);
    }

    function tabClosed(data){
        onCloseTab(data);
    }

    function addTab(){
        onAddTab();
    }

    return (
        <div className="rb-tabs-container">
            <div className="header">
                <div className="tabs">
                    <TabsList
                        tabs={tabs}
                        current={current}
                        handleDragEnd={onSort}
                        onTabChange={tabChange}
                        onCloseTab={tabClosed}
                        sortable={sortable}
                    />
                </div>
                <div className="controls">
                    {!additionDisabled &&
                        <div className="add-tab" onClick={addTab}>
                            <Icon icon="plus"/>
                        </div>
                    }
                </div>
            </div>
            <div className="current-tab-content">
                { currentTabContent() }
            </div>
        </div>
    );
}
