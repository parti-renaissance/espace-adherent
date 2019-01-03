import { connect } from 'react-redux';
import { showModal } from '../../redux/actions/modal';
import Header from '../../components/Header';

import { MY_IDEAS_MODAL } from '../../constants/modalTypes';

export default connect(
    null,
    { onMyIdeasBtnClicked: tabActive => showModal(MY_IDEAS_MODAL, { tabActive }) }
)(Header);
