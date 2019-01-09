import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import FlagModal from '../../components/Modal/FlagModal';
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

function mapStateToProps(state) {
    // get static data
    const { reasons } = selectStatic(state);
    // const reasons = {
    //     en_marche_values: 'Ce que je vois ne correspond pas aux valeurs du Mouvement',
    //     inappropriate: 'Ce n\'est pas du contenu approprié',
    //     commercial_content: 'Il s\'agit de contenu commercial',
    //     other: 'Autre',
    // };
    const formattedReasons = Object.entries(reasons).map(([value, label]) => ({
        value,
        label,
    }));
    return {
        reasons: formattedReasons,
    };
}

export default connect(
    mapStateToProps,
    null
)(FlagModalContainer);
