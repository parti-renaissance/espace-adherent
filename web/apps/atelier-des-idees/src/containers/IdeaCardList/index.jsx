import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { ideaStatus } from '../../constants/api';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectIdeasWithStatus, selectIdeasMetadata } from '../../redux/selectors/ideas';
import { fetchNextIdeas } from '../../redux/thunk/ideas';
import Button from '../../components/Button';
import IdeaCardList from '../../components/IdeaCardList';

function IdeaCardListContainer(props) {
    return (
        <React.Fragment>
            <IdeaCardList ideas={props.ideas} isLoading={props.isLoading} mode={props.mode} />
            {props.withPaging && (
                <div className="idea-card-list__paging">
                    <Button label="Plus d'idées" mode="tertiary" onClick={props.onMoreClicked} />
                </div>
            )}
        </React.Fragment>
    );
}

IdeaCardListContainer.defaultProps = {
    onMoreClicked: undefined,
    withPaging: false,
};

IdeaCardListContainer.propTypes = {
    onMoreClicked: PropTypes.func,
    status: PropTypes.oneOf(ideaStatus).isRequired,
    withPaging: PropTypes.bool,
};

function mapStateToProps(state, ownProps) {
    const isLoading = selectLoadingState(state, `FETCH_IDEAS_${ownProps.status}`);
    const ideas = selectIdeasWithStatus(state, ownProps.status);
    /* paging data */
    const { current_page, last_page } = selectIdeasMetadata(state);
    // show paging if props says so and is not loading and is not at the end of the list
    const withPaging = ownProps.withPaging && current_page < last_page && !isLoading;
    return { ideas, isLoading: isLoading && !ideas.length, withPaging };
}

function mapDispatchToProps(dispatch, ownProps) {
    return {
        onMoreClicked: () => dispatch(fetchNextIdeas(ownProps.status)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaCardListContainer);
