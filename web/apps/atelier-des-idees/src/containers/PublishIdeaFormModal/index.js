import { connect } from 'react-redux';
import PublishIdeaFormModal from '../../components/Modal/PublishIdeaFormModal';
import { selectLoadingState } from '../../redux/selectors/loading';

function mapStateToProps(state, { id }) {
    // get request status
    const { isFetching, isSuccess, isError } = selectLoadingState(state, 'PUBLISH_IDEA', id);
    // TODO: fetch static data
    return {
        isSubmitSuccess: isSuccess,
        isSubmitError: isError,
        themeOptions: [
            { value: 'agriculture', label: 'Agriculture' },
            { value: 'education', label: 'Education' },
            { value: 'culture', label: 'Culture' },
            { value: 'defense', label: 'Défense' },
            { value: 'parity', label: 'Parité' },
        ],
        localityOptions: [{ value: 'national', label: 'National' }, { value: 'european', label: 'Européen' }],
        authorOptions: [{ value: 'alone', label: 'Seul' }, { value: 'committee', label: 'Mon comité' }],
        committeeOptions: [{ value: 'comittee_1', label: 'Comité 1' }, { value: 'comittee_2', label: 'Comité 2' }],
        difficultiesOptions: [{ value: 'juridique', label: 'Juridique' }, { value: 'finance', label: 'Finance' }],
    };
}

export default connect(
    mapStateToProps,
    {}
)(PublishIdeaFormModal);
