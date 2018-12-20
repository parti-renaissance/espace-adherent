import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';

const LatestIdeasPane = props => (
    <div className="latest-ideas__pane">
        {/* IdeasCardList */}
        {props.isLoading && 'Loading...'}
        <div className="latest-ideas__pane__footer">
            <Link to={props.link} className="button button--tertiary latest-ideas__pane__footer__btn">
                Voir toutes les propositions
            </Link>
        </div>
    </div>
);

LatestIdeasPane.defaultProps = {
    ideas: [],
    isLoading: false,
};

LatestIdeasPane.propTypes = {
    ideas: PropTypes.array,
    isLoading: PropTypes.bool,
    link: PropTypes.string.isRequired,
};

export default LatestIdeasPane;
