import { connect } from 'react-redux';
import { showModal } from '../../redux/actions/modal';
import { selectReports } from '../../redux/selectors/reports';
import Reports from '../../components/Reports';

import { REPORTS_MODAL } from '../../constants/modalTypes';

function mapStateToProps(state) {
    const reports = selectReports(state);
    return { reports };
}

export default connect(
    mapStateToProps,
    { onReportBtnClicked: reports => showModal(REPORTS_MODAL, { reports }) }
)(Reports);
