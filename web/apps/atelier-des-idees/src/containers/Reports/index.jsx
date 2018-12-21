import { connect } from 'react-redux';
import { showModal } from '../../redux/actions/modal';
import Reports from '../../components/Reports';

export default connect(
    null,
    { showModal }
)(Reports);
