import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL, PUBLISH_IDEA_MODAL, FLAG_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { initIdeaPage } from '../../redux/thunk/navigation';
import {
    saveCurrentIdea,
    publishCurrentIdea,
    deleteCurrentIdea,
    goBackFromCurrentIdea,
} from '../../redux/thunk/currentIdea';
import { reportIdea } from '../../redux/thunk/ideas';
import { selectAuthUser, selectIsAuthenticated } from '../../redux/selectors/auth';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectCurrentIdea, selectGuidelines } from '../../redux/selectors/currentIdea';

class IdeaPage extends React.Component {
    componentDidMount() {
        window.scrollTo(0, 0);
        this.props.initIdeaPage();
    }

    render() {
        if (this.props.hasFetchError) {
            // redirect to home is error in fetch
            return <Redirect to="/atelier-des-idees" />;
        }
        return (
            <IdeaPageBase
                {...this.props}
                isLoading={this.props.isFetchingIdea || !this.props.guidelines.length}
                key={`idea-page__${this.props.isFetchingIdea || !this.props.guidelines.length}`}
            />
        );
    }
}

IdeaPage.defaultProps = {
    hasFetchError: false,
    isFetchingIdea: false,
    guidelines: [],
};

IdeaPage.propTypes = {
    initIdeaPage: PropTypes.func.isRequired,
    isFetchingIdea: PropTypes.bool,
    hasFetchError: PropTypes.bool,
};

function mapStateToProps(state, ownProps) {
    const { id } = ownProps.match.params;
    const fetchIdeaState = selectLoadingState(state, 'FETCH_IDEA', id);
    // data
    const currentUser = selectAuthUser(state);
    // guidelines
    const guidelines = selectGuidelines(state);
    // get and format current idea
    const idea = selectCurrentIdea(state);
    const { author = {}, published_at, ...ideaData } = idea;
    const formattedIdea = {
        ...ideaData,
        authorName: author ? `${author.first_name} ${author.last_name}` : '',
        publishedAt: published_at && new Date(published_at).toLocaleDateString(),
    };
    const isAuthenticated = selectIsAuthenticated(state);

    return {
        idea: formattedIdea,
        guidelines,
        isAuthor: !!author.uuid && author.uuid === currentUser.uuid,
        isAuthenticated,
        hasFetchError: fetchIdeaState.isError,
        isFetchingIdea: fetchIdeaState.isFetching,
    };
}

function mapDispatchToProps(dispatch, ownProps) {
    return {
        initIdeaPage: () => {
            const { id } = ownProps.match.params;
            dispatch(initIdeaPage(id));
        },
        onBackClicked: () => dispatch(goBackFromCurrentIdea()),
        onPublishIdea: (data) => {
            const { id } = ownProps.match.params;
            dispatch(
                showModal(PUBLISH_IDEA_MODAL, {
                    id,
                    submitForm: ideaData => dispatch(publishCurrentIdea({ ...ideaData, ...data }, true)),
                })
            );
        },
        onDeleteClicked: () =>
            dispatch(
                showModal(DELETE_IDEA_MODAL, {
                    onConfirmDelete: () => dispatch(deleteCurrentIdea()),
                })
            ),
        onReportClicked: () => {
            const { id } = ownProps.match.params;
            dispatch(
                showModal(FLAG_MODAL, {
                    onSubmit: data => dispatch(reportIdea(data, id)),
                    id,
                })
            );
        },
        onSaveIdea: data => dispatch(saveCurrentIdea(data)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaPage);
