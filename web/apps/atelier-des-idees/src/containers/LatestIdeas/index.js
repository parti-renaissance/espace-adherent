import { connect } from 'react-redux';
import { ideaStatus } from '../../constants/api';
import LatestIdeas from '../../components/LatestIdeas';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectIdeasWithStatus } from '../../redux/selectors/ideas';

const mapStateToProps = (state) => {
    const isLoadingFinalizedIdeas = selectLoadingState(state, 'FETCH_IDEAS_FINALIZED');
    const isLoadingPendingIdeas = selectLoadingState(state, 'FETCH_IDEAS_PENDING');
    // get ideas
    const finalizedIdeas = selectIdeasWithStatus(state, ideaStatus.FINALIZED);
    const pendingIdeas = selectIdeasWithStatus(state, ideaStatus.PENDING);
    return {
        ideas: {
            finalized: { isLoading: isLoadingFinalizedIdeas, items: finalizedIdeas },
            pending: { isLoading: isLoadingPendingIdeas, items: pendingIdeas },
        },
    };
};

export default connect(
    mapStateToProps,
    {}
)(LatestIdeas);
