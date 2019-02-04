import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { Link } from 'react-router-dom';

import icn_toggle_content from './../../../../img/icn_toggle_content-blue-yonder.svg';

class MyContributions extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            showList: true,
        };
        this.categoryData = {
            label: 'vous avez contribué',
        };
    }

    render() {
        return (
            <div className="my-contributions">
                {this.props.ideas.length ? (
                    <button
                        className="my-contributions__category__button"
                        onClick={() =>
                            this.setState(prevState => ({
                                showList: !prevState.showList,
                            }))
                        }
                    >
                        <span className="my-contributions__category__button__label">{this.categoryData.label}</span>
                        <img
                            className={classNames('my-contributions__category__button__icon', {
                                'my-contributions__category__button__icon--rotate': !this.state.showList,
                            })}
                            src={icn_toggle_content}
                        />
                    </button>
                ) : (
                    <p className="my-contributions__category__button__label">{this.categoryData.label}</p>
                )}
                {this.props.ideas.length ? (
                    this.props.ideas.map(
                        idea =>
                            this.state.showList && (
                                <div className="my-contributions__category__idea">
                                    <p className="my-contributions__category__idea__date">
                                        Créée le {new Date(idea.created_at).toLocaleDateString()}
                                    </p>
                                    <h4 className="my-contributions__category__idea__name">{idea.name}</h4>
                                    <div className="my-contributions__category__idea__actions">
                                        <Link
                                            to={`/atelier-des-idees/proposition/${idea.uuid}`}
                                            className="my-contributions__category__idea__actions__see-note button--secondary"
                                        >
                                            Voir la proposition
                                        </Link>
                                    </div>
                                </div>
                            )
                    )
                ) : (
                    <p className="my-contributions__category__empty-label">Vous n’avez pas encore de contribution</p>
                )}
            </div>
        );
    }
}

MyContributions.propTypes = {
    ideas: PropTypes.arrayOf(
        PropTypes.shape({
            uuid: PropTypes.string.isRequired,
            name: PropTypes.string.isRequired,
            created_at: PropTypes.string.isRequired, // ISO UTC
        })
    ).isRequired,
};

export default MyContributions;
