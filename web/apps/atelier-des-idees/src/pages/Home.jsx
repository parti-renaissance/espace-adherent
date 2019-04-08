import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import ConsultationPinned from '../containers/ConsultationPinned';
import MovementIdeas from '../components/MovementIdeas';
import Header from '../containers/Header';
import LatestIdeas from '../components/LatestIdeas';
import Reports from '../containers/Reports';
import { initHomePage } from '../redux/thunk/navigation';
import { setIdeas } from '../redux/actions/ideas';

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
                    <MovementIdeas ideas={this.props.ideas} />
                    <LatestIdeas ideas={this.props.ideas} />
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

const mapStateToProps = state => ({ ideas: state.ideas });

export default connect(
    mapStateToProps,
    {
        initHomePage,
        setIdeas,
    }
)(Home);
