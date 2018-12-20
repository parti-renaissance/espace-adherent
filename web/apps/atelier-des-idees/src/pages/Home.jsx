import React from 'react';
import PropTypes from 'prop-types';

import MovementIdeas from '../components/MovementIdeas';
import LatestIdeas from '../components/LatestIdeas';
import Reports from '../components/Reports';

function Home(props) {
    return (
        <div className="home-page">
            <MovementIdeas />
            <LatestIdeas />
            <Reports />
        </div>
    );
}

export default Home;
