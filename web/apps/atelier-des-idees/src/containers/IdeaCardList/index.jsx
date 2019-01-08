import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { ideaStatus } from '../../constants/api';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectIdeasWithStatus, selectIdeasMetadata } from '../../redux/selectors/ideas';
import { fetchNextIdeas, voteIdea } from '../../redux/thunk/ideas';
import Button from '../../components/Button';
import IdeaCardList from '../../components/IdeaCardList';

function IdeaCardListContainer(props) {
    return props.ideas.length ? (
        <React.Fragment>
            <IdeaCardList
                ideas={props.ideas}
                isLoading={props.isLoading}
                mode={props.mode}
                onVoteIdea={props.onVoteIdea}
            />
            {props.withPaging && (
                <div className="idea-card-list__paging">
                    <Button label="Plus d'idées" mode="tertiary" onClick={props.onMoreClicked} />
                </div>
            )}
        </React.Fragment>
    ) : (
        <div className="idea-card-list__empty">
            <img className="idea-card-list__empty__img" src="/assets/img/no-idea-result.svg" />
            <p>Il n'y a pas d'idée correspondant à votre recherche</p>
        </div>
    );
}

IdeaCardListContainer.defaultProps = {
    onMoreClicked: undefined,
    withPaging: false,
};

IdeaCardListContainer.propTypes = {
    onMoreClicked: PropTypes.func,
    status: PropTypes.oneOf(Object.keys(ideaStatus)).isRequired,
    withPaging: PropTypes.bool,
};

function mapStateToProps(state, ownProps) {
    const { isFetching } = selectLoadingState(state, 'FETCH_IDEAS', ownProps.status);
    const ideas = selectIdeasWithStatus(state, ownProps.status);
    /* paging data */
    const { current_page, last_page } = selectIdeasMetadata(state);
    // show paging if props says so and is not loading and is not at the end of the list
    const withPaging = ownProps.withPaging && current_page < last_page && !isFetching;
    return { ideas: [], isLoading: isFetching && !ideas.length, withPaging };
}

function mapDispatchToProps(dispatch, ownProps) {
    return {
        onMoreClicked: () => dispatch(fetchNextIdeas(ownProps.status)),
        onVoteIdea: (id, vote) => dispatch(voteIdea(id, vote)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaCardListContainer);
