import { connect } from 'react-redux';
import LatestIdeas from '../../components/LatestIdeas';
import { selectLoadingState } from '../../redux/selectors/loading';

const mapStateToProps = (state) => {
    const isLoadingFinalizedIdeas = selectLoadingState(state, 'FETCH_IDEAS_finalized');
    const isLoadingPendingIdeas = selectLoadingState(state, 'FETCH_IDEAS_pending');
    // TODO: get items
    return {
        ideas: {
            finalized: { isLoading: isLoadingFinalizedIdeas, items: [] },
            pending: { isLoading: isLoadingPendingIdeas, items: [] },
        },
    };
};

export default connect(
    mapStateToProps,
    {}
)(LatestIdeas);
