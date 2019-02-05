import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import PublishIdeaFormModal from '../../components/Modal/PublishIdeaFormModal';
import { fetchStaticData } from '../../redux/thunk/static';
import { resetLoading } from '../../redux/actions/loading';
import { selectAuthUser } from '../../redux/selectors/auth';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectStatic } from '../../redux/selectors/static';
import { selectCurrentIdea } from '../../redux/selectors/currentIdea';

class PublishFormModalContainer extends React.Component {
    componentDidMount() {
        this.props.initPublishForm();
    }

    componentWillUnmount() {
        this.props.unmountPublishForm();
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

/**
 * Get default values from options based on selectedItems
 * @param {array} selectedItems Array of selected items
 * @param {array} options Array of formatted options (see function above)
 */
function getDefaultValues(selectedItems = [], options = []) {
    const selectedIds = selectedItems.map(item => item.id);
    return options.filter(option => selectedIds.includes(option.value));
}

function mapStateToProps(state, { id }) {
    // user info
    const currentUser = selectAuthUser(state);
    // console.warn()
    // get request status
    const currentIdea = selectCurrentIdea(state);
    const saveIdeaState = selectLoadingState(state, 'SAVE_CURRENT_IDEA', id);
    const publishIdeaState = selectLoadingState(state, 'PUBLISH_IDEA');
    // get static data
    const { themes, needs, categories, committees } = selectStatic(state);
    const formattedThemes = formatStaticData(themes);
    const formattedNeeds = formatStaticData(needs);
    const formattedCategories = formatStaticData(categories);
    const formattedCommittees = committees.map(({ uuid, name }) => ({ value: uuid, label: name }));
    const authorOptions = [{ value: 'alone', label: 'Seul' }, { value: 'committee', label: 'Mon comité' }];
    // get default values
    const selectedThemes = getDefaultValues(currentIdea.themes, formattedThemes);
    const selectedNeeds = getDefaultValues(currentIdea.needs, formattedNeeds);
    const selectedCategory =
        currentIdea.category && formattedCategories.find(option => option.value === currentIdea.category.id);
    const committeeId = currentIdea.committee && currentIdea.committee.uuid;
    const authorType = committeeId ? 'committee' : 'alone';
    return {
        isSubmitting: saveIdeaState.isFetching || publishIdeaState.isFetching,
        isSubmitSuccess: saveIdeaState.isSuccess && publishIdeaState.isSuccess,
        isSubmitError: saveIdeaState.isError || publishIdeaState.isError,
        id: id || currentIdea.uuid,
        defaultValues: {
            description: currentIdea.description,
            theme: selectedThemes,
            locality: selectedCategory,
            difficulties: selectedNeeds,
            author:
                currentIdea.author && currentIdea.author.uuid
                    ? authorOptions.filter(option => option.value === authorType)
                    : [],
            committee: committeeId,
        },
        themeOptions: formattedThemes,
        localityOptions: formattedCategories,
        difficultiesOptions: formattedNeeds,
        committeeOptions: formattedCommittees,
        authorOptions,
        canSelectAuthor: !(currentUser.elected || currentUser.larem),
    };
}

function mapDispatchToProps(dispatch) {
    return {
        initPublishForm: () => {
            dispatch(resetLoading());
            dispatch(fetchStaticData());
        },
        // reset loading states on unmount to avoid side effect
        unmountPublishForm: () => dispatch(resetLoading()),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(PublishFormModalContainer);
