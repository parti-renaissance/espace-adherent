import React from 'react';
import PropTypes from 'prop-types';
import MovementIdeas from '../components/MovementIdeas/.';
import LatestIdeas from '../components/LatestIdeas/.';

function Home(props) {
    return (
        <div className="home-page">
            <MovementIdeas/>
            <LatestIdeas />
        </div>
    );
}

export default Home;