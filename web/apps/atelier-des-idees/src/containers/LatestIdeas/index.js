import { connect } from 'react-redux';
import LatestIdeas from '../../components/LatestIdeas';
import { selectLoadingState } from '../../redux/selectors/loading';

const mapStateToProps = (state) => {
    const isLoadingPublishedIdeas = selectLoadingState(state, 'FETCH_IDEAS_published');
    const isLoadingPendingIdeas = selectLoadingState(state, 'FETCH_IDEAS_pending');
    // TODO: get items
    return {
        ideas: {
            published: { isLoading: isLoadingPublishedIdeas, items: [] },
            pending: { isLoading: isLoadingPendingIdeas, items: [] },
        },
    };
};

export default connect(
    mapStateToProps,
    {}
)(LatestIdeas);
