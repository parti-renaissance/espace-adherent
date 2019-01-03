import { connect } from 'react-redux';
import PublishIdeaFormModal from '../../components/Modal/PublishIdeaFormModal';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectStatic } from '../../redux/selectors/static';

function formatStaticData(data) {
    return data.map(({ id, name }) => ({ value: id, label: name }));
}

function mapStateToProps(state, { id }) {
    // get request status
    const { isFetching, isSuccess, isError } = selectLoadingState(state, 'PUBLISH_IDEA', id);
    // get static data
    const { themes, needs, categories, committees } = selectStatic(state);
    const formattedCommittees = committees.map(({ uuid, name }) => ({ value: uuid, label: name }));
    return {
        isSubmitSuccess: isSuccess,
        isSubmitError: isError,
        themeOptions: formatStaticData(themes),
        localityOptions: formatStaticData(categories),
        difficultiesOptions: formatStaticData(needs),
        committeeOptions: formattedCommittees,
        authorOptions: [{ value: 'alone', label: 'Seul' }, { value: 'committee', label: 'Mon comité' }],
    };
}

export default connect(
    mapStateToProps,
    {}
)(PublishIdeaFormModal);
