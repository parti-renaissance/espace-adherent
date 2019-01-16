import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import FlagModal from '../../components/Modal/FlagModal';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectStatic } from '../../redux/selectors/static';

class FlagModalContainer extends React.Component {
    render() {
        return <FlagModal {...this.props} />;
    }
}

FlagModalContainer.propTypes = {
    onSubmit: PropTypes.func.isRequired,
    reasons: PropTypes.array.isRequired,
};

function mapStateToProps(state, { id }) {
    // get static data
    const { reasons } = selectStatic(state);
    // const reasons = {
    //     en_marche_values: 'Ce que je vois ne correspond pas aux valeurs du Mouvement',
    //     inappropriate: 'Ce n\'est pas du contenu approprié',
    //     commercial_content: 'Il s\'agit de contenu commercial',
    //     other: 'Autre',
    // };
    // get request status
    const flagState = selectLoadingState(state, 'POST_FLAG', id);
    const formattedReasons = Object.entries(reasons).map(([value, label]) => ({
        value,
        label,
    }));
    return {
        reasons: formattedReasons,
        isSubmitSuccess: flagState.isSuccess,
        isSubmitError: flagState.isError,
    };
}

export default connect(
    mapStateToProps,
    null
)(FlagModalContainer);
