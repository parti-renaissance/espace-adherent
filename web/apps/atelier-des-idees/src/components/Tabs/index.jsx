import React from 'react';
import PropTypes from 'prop-types';
import RcTabs, { TabPane } from 'rc-tabs';
import TabContent from 'rc-tabs/lib/TabContent';
import ScrollableInkTabBar from 'rc-tabs/lib/ScrollableInkTabBar';

import 'rc-tabs/assets/index.css';
import { getTransformByIndex } from 'rc-tabs/lib/utils';

// https://github.com/fis-components/rc-tabs

function Tabs(props) {
    return (
        <RcTabs
            className="tabs"
            renderTabBar={() => <ScrollableInkTabBar />}
            renderTabContent={() => <TabContent />}
            defaultActiveKey={props.defaultActiveKey}
        >
            {props.panes.map((pane, index) => {
                const Component = pane.component;
                return (
                    <TabPane tab={pane.title} key={index}>
                        {'function' === typeof Component ? <Component /> : Component}
                    </TabPane>
                );
            })}
        </RcTabs>
    );
}

Tabs.defaultProps = {
    defaultActiveKey: '0',
};

Tabs.propTypes = {
    panes: PropTypes.arrayOf(
        PropTypes.shape({
            title: PropTypes.string.isRequired,
            component: PropTypes.oneOfType([
                PropTypes.node.isRequired,
                PropTypes.func,
            ]),
        })
    ).isRequired,
    defaultActiveKey: PropTypes.string,
};

export default Tabs;
