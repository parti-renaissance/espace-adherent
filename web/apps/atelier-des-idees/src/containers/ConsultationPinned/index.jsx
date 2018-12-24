import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { selectShowConsultationPinned, selectConsultationPinned } from '../../redux/selectors/pinned';
import { hideConsultationPinned } from '../../redux/actions/pinned';
import Banner from '../../components/Banner';

class BannerContainer extends React.Component {
    shouldComponentUpdate(nextProps, nextState) {
        // only render if hide or show banner
        return this.props.showConsultationPinned !== nextProps.showConsultationPinned;
    }

    render() {
        const { data, showConsultationPinned, hideBanner } = this.props;
        const { title, link, calendar, duration } = data;
        return showConsultationPinned ? <Banner {...data} onClose={hideBanner} /> : null;
    }
}

BannerContainer.propTypes = {
    data: PropTypes.object.isRequired, // cf Banner props
    hideBanner: PropTypes.func.isRequired,
    showConsultationPinned: PropTypes.bool.isRequired,
};

function mapStateToProps(state) {
    const showConsultationPinned = selectShowConsultationPinned(state);
    const consultationPinnedData = selectConsultationPinned(state);
    // format data to match Banner props
    const { title, link, calendar, duration } = consultationPinnedData;
    return { showConsultationPinned, data: { title, link, subtitle: calendar, extraInfo: duration } };
}

export default connect(
    mapStateToProps,
    {
        hideBanner: hideConsultationPinned,
    }
)(BannerContainer);
