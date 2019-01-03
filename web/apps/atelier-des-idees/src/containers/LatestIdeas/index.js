import { connect } from 'react-redux';
import { ideaStatus } from '../../constants/api';
import LatestIdeas from '../../components/LatestIdeas';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectIdeasWithStatus } from '../../redux/selectors/ideas';

/**
 * Sort ideas by creation date DESC
 * @param {array} ideas Ideas to be sorted
 */
function sortIdeasByDate(ideas = []) {
    return ideas.sort((a, b) => {
        if (a.created_at < b.created_at) {
            return 1;
        }
        if (a.created_at > b.created_at) {
            return -1;
        }
        return 0;
    });
}

const mapStateToProps = (state) => {
    const isLoadingFinalizedIdeas = selectLoadingState(state, 'FETCH_IDEAS_FINALIZED').isFetching;
    const isLoadingPendingIdeas = selectLoadingState(state, 'FETCH_IDEAS_PENDING').isFetching;
    // get ideas
    const finalizedIdeas = selectIdeasWithStatus(state, ideaStatus.FINALIZED);
    const pendingIdeas = selectIdeasWithStatus(state, ideaStatus.PENDING);
    return {
        ideas: {
            finalized: { isLoading: isLoadingFinalizedIdeas, items: sortIdeasByDate(finalizedIdeas) },
            pending: { isLoading: isLoadingPendingIdeas, items: sortIdeasByDate(pendingIdeas) },
        },
    };
};

export default connect(
    mapStateToProps,
    {}
)(LatestIdeas);
