import { connect } from 'react-redux';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectIdeasWithStatus } from '../../redux/selectors/ideas';
import IdeaCardList from '../../components/IdeaCardList';

function mapStateToProps(state, ownProps) {
    const isLoading = selectLoadingState(state, `FETCH_IDEAS_${ownProps.status}`);
    const ideas = selectIdeasWithStatus(state, ownProps.status);
    return { ideas, isLoading };
}

export default connect(
    mapStateToProps,
    {}
)(IdeaCardList);
