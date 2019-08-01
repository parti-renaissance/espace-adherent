import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { hideHeader, showHeader } from '../redux/actions/ui';

export default (ChildComponent) => {
    class WithoutHeader extends React.Component {
        componentDidMount() {
            this.props.hideHeader();
        }

        componentWillUnmount() {
            this.props.showHeader();
        }

        render() {
            return <ChildComponent {...this.props} />;
        }
    }
    const withoutHeader = props => <ChildComponent {...props} />;

    withoutHeader.propTypes = {
        showHeader: PropTypes.func.isRequired,
        hideHeader: PropTypes.func.isRequired,
    };

    return connect(
        null,
        { showHeader, hideHeader }
    )(WithoutHeader);
};
