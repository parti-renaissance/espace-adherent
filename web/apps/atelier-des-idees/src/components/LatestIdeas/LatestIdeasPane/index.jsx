import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';
import IdeaCardList from '../../IdeaCardList';

const LatestIdeasPane = props => (
    <div className="latest-ideas__pane">
        <IdeaCardList
            ideas={props.ideas}
            isLoading={props.isLoading}
            onVoteIdea={props.onVoteIdea}
            readIdeas={props.readIdeas}
        />
        <div className="latest-ideas__pane__footer">
            <Link to={props.link} className="button button--tertiary latest-ideas__pane__footer__btn">
                Voir toutes les propositions
            </Link>
        </div>
    </div>
);

LatestIdeasPane.defaultProps = {
    ideas: [],
    readIdeas: [],
    isLoading: false,
};

LatestIdeasPane.propTypes = {
    ideas: PropTypes.array,
    isLoading: PropTypes.bool,
    link: PropTypes.string.isRequired,
    onVoteIdea: PropTypes.func,
    readIdeas: PropTypes.arrayOf(PropTypes.string), // array of uuids
};

export default LatestIdeasPane;
