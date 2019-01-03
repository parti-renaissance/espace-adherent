import { connect } from 'react-redux';
import PublishIdeaFormModal from '../../components/Modal/PublishIdeaFormModal';

function mapStateToProps(state) {
    // TODO: fetch static data
    // TODO: get submit status
    return {
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
