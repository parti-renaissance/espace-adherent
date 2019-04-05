import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import ConsultationPinned from '../containers/ConsultationPinned';
import MovementIdeas from '../components/MovementIdeas';
import Header from '../containers/Header';
import LatestIdeas from '../containers/LatestIdeas';
import Reports from '../containers/Reports';
import { initHomePage } from '../redux/thunk/navigation';
import { setIdeas } from '../redux/actions/ideas';
import { ideaStatus } from '../constants/api';
import { selectIdeasWithStatus } from '../redux/selectors/ideas';

class Home extends React.Component {
    componentDidMount() {
        window.scrollTo(0, 0);
        this.props.initHomePage();
    }

    render() {
        return (
            <React.Fragment>
                <Header />
                <div className="home-page">
                    <ConsultationPinned />
                    <MovementIdeas totalCount={this.props.ideas} />
                    <LatestIdeas />
                    <Reports />
                </div>
            </React.Fragment>
        );
    }
}

Home.propTypes = {
    initHomePage: PropTypes.func.isRequired,
    setIdeas: PropTypes.func.isRequired,
};

const mapStateToProps = (state) => {
    // get ideas
    const finalizedIdeas = selectIdeasWithStatus(state, ideaStatus.FINALIZED);
    const pendingIdeas = selectIdeasWithStatus(state, ideaStatus.PENDING);

    return {
        ideas: {
            finalized: {
                items: finalizedIdeas,
            },
            pending: {
                items: pendingIdeas,
            },
        },
    };
};

export default connect(
    mapStateToProps,
    {
        initHomePage,
        setIdeas,
    }
)(Home);
