import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import FlagModal from '../../components/Modal/FlagModal';
import { selectStatic } from '../../redux/selectors/static';

class FlagModalContainer extends React.Component {
    render() {
        return <FlagModal {...this.props} onSubmit={data => console.log(data)} />;
    }
}

FlagModalContainer.propTypes = {
    reasons: PropTypes.array.isRequired,
};

function mapStateToProps(state) {
    // get static data
    const { reasons } = selectStatic(state);
    return {
        reasons,
    };
}

export default connect(
    mapStateToProps,
    null
)(FlagModalContainer);
