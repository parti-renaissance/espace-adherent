import { connect } from 'react-redux';
import { showModal } from '../../redux/actions/modal';
import { selectReports } from '../../redux/selectors/reports';
import Reports from '../../components/Reports';

function mapStateToProps(state) {
    const reports = selectReports(state);
    return { reports };
}

export default connect(
    mapStateToProps,
    { showModal }
)(Reports);
