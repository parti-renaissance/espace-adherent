import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import PublishIdeaFormModal from '../../components/Modal/PublishIdeaFormModal';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectStatic } from '../../redux/selectors/static';
import { fetchStaticData } from '../../redux/thunk/static';
import { selectCurrentIdea } from '../../redux/selectors/currentIdea';

class PublishFormModalContainer extends React.Component {
    componentDidMount() {
        this.props.initPublishForm();
    }

    render() {
        const { initPublishForm, ...otherProps } = this.props;
        return <PublishIdeaFormModal {...otherProps} />;
    }
}

PublishFormModalContainer.propTypes = {
    initPublishForm: PropTypes.func.isRequired,
};

function formatStaticData(data) {
    return data.map(({ id, name }) => ({ value: id, label: name }));
}

function mapStateToProps(state, { id }) {
    // get request status
    const currentIdea = selectCurrentIdea(state);
    const saveIdeaState = selectLoadingState(state, 'SAVE_IDEA', id);
    const publishIdeaState = selectLoadingState(state, 'PUBLISH_IDEA');
    // get static data
    const { themes, needs, categories, committees } = selectStatic(state);
    const formattedCommittees = committees.map(({ uuid, name }) => ({ value: uuid, label: name }));
    return {
        isSubmitting: saveIdeaState.isFetching || publishIdeaState.isFetching,
        isSubmitSuccess: saveIdeaState.isSuccess && publishIdeaState.isSuccess,
        isSubmitError: saveIdeaState.isError || publishIdeaState.isError,
        id: id || currentIdea.uuid,
        themeOptions: formatStaticData(themes),
        localityOptions: formatStaticData(categories),
        difficultiesOptions: formatStaticData(needs),
        committeeOptions: formattedCommittees,
        authorOptions: [{ value: 'alone', label: 'Seul' }, { value: 'committee', label: 'Mon comité' }],
    };
}

function mapDispatchToProps(dispatch) {
    return {
        initPublishForm: () => dispatch(fetchStaticData()),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(PublishFormModalContainer);
