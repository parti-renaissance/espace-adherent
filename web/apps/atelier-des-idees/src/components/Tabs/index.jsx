import React from 'react';
import PropTypes from 'prop-types';
import RcTabs, { TabPane } from 'rc-tabs';
import TabContent from 'rc-tabs/lib/TabContent';
import ScrollableInkTabBar from 'rc-tabs/lib/ScrollableInkTabBar';

import 'rc-tabs/assets/index.css';


function Tabs(props) {
    return (
        <RcTabs
            className="tabs"
            renderTabBar={ () => <ScrollableInkTabBar /> }
            renderTabContent={ () => <TabContent /> }>
            {props.panes.map((pane, index) => {
                const Component = pane.component;
                return <TabPane tab={pane.title} key={index}>{'function' === typeof Component ? <Component /> : Component}</TabPane>;
            })}
        </RcTabs>
    );
}

Tabs.propTypes = {
    panes: PropTypes.arrayOf(PropTypes.shape({
        title: PropTypes.string.isRequired,
        component: PropTypes.oneOfType([PropTypes.node.isRequired, PropTypes.func]),
    })).isRequired,
};

export default Tabs;