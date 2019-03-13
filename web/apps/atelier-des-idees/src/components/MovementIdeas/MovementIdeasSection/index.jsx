import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';

class MovementIdeasSection extends React.PureComponent {
    render() {
        return (
            <div className="movement-ideas__section__item">
                <h4 className="movement-ideas__section__item__title">
          Je <span className="movement-ideas__section__item__title__main">{this.props.keyWord}</span> {this.props.title}
                </h4>
                <p className="movement-ideas__section__item__content">{this.props.text}</p>
                <Link to={this.props.link} className="button button--secondary movement-ideas__section__item__link">
                    {this.props.linkLabel}
                </Link>
            </div>
        );
    }
}

MovementIdeasSection.propTypes = {
    title: PropTypes.string.isRequired,
    keyWord: PropTypes.string.isRequired,
    text: PropTypes.string.isRequired,
    linkLabel: PropTypes.string.isRequired,
    link: PropTypes.string.isRequired,
};

export default MovementIdeasSection;
