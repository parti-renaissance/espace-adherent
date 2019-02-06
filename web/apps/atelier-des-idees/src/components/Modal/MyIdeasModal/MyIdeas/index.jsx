import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { Link } from 'react-router-dom';
import { ideaStatus } from '../../../../constants/api';
import Button from '../../../Button';
import icn_toggle_content from './../../../../img/icn_toggle_content-blue-yonder.svg';

class MyIdeas extends React.Component {
    constructor(props) {
        super(props);
        this.CAT_IDEAS_FILTER = [
            {
                showCat: 'showDraft',
                label: 'brouillons',
                empty: 'brouillon',
                ideas: this.props.ideas.filter(idea => 'DRAFT' === idea.status),
            },
            {
                showCat: 'showPending',
                label: 'propositions en cours d’élaboration',
                empty: 'proposition en cours d’élaboration',
                ideas: this.props.ideas.filter(idea => 'PENDING' === idea.status),
            },
            {
                showCat: 'showFinalized',
                label: 'propositions finalisées',
                empty: 'proposition finalisée',
                ideas: this.props.ideas.filter(idea => 'FINALIZED' === idea.status),
            },
        ];

        this.state = {
            showDraft: true,
            showPending: true,
            showFinalized: true,
        };
    }

    render() {
        return (
            <div className="my-ideas">
                {this.CAT_IDEAS_FILTER.map((cat) => {
                    const categoryHeader = cat.ideas.length ? (
                        <button
                            className="my-ideas__category__button"
                            onClick={() =>
                                this.setState(prevState => ({
                                    [cat.showCat]: !prevState[cat.showCat],
                                }))
                            }
                        >
                            <span className="my-ideas__category__button__label">{cat.label.toUpperCase()}</span>
                            <img
                                className={classNames('my-ideas__category__button__icon', {
                                    'my-ideas__category__button__icon--rotate': !this.state[cat.showCat],
                                })}
                                src={icn_toggle_content}
                            />
                        </button>
                    ) : (
                        <p className="my-ideas__category__button__label">{cat.label.toUpperCase()}</p>
                    );
                    return (
                        <div className="my-ideas__category">
                            {categoryHeader}
                            {cat.ideas.length ? (
                                cat.ideas.map(
                                    idea =>
                                        this.state[cat.showCat] && (
                                            <div className="my-ideas__category__idea">
                                                <p className="my-ideas__category__idea__date">
                                                    Créée le {new Date(idea.created_at).toLocaleDateString()}
                                                </p>
                                                <h4 className="my-ideas__category__idea__name">
                                                    <Link to={`/atelier-des-idees/proposition/${idea.uuid}`}>
                                                        {idea.name}
                                                    </Link>
                                                </h4>
                                                <div className="my-ideas__category__idea__actions">
                                                    <Link
                                                        to={`/atelier-des-idees/proposition/${idea.uuid}`}
                                                        className="my-ideas__category__idea__actions__edit"
                                                    >
                                                        {'FINALIZED' !== idea.status && 'Editer'}
                                                        {'FINALIZED' === idea.status && 'Voir la proposition'}
                                                    </Link>
                                                    <button className="button my-ideas__category__idea__actions__delete" onClick={() => this.props.onDeleteIdea(idea.uuid)}>
                                                        Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                        )
                                )
                            ) : (
                                <p className="my-ideas__category__empty-label">{`Vous n'avez pas de ${cat.empty}`}</p>
                            )}
                        </div>
                    );
                })}
            </div>
        );
    }
}

MyIdeas.propTypes = {
    ideas: PropTypes.arrayOf(
        PropTypes.shape({
            uuid: PropTypes.string.isRequired,
            name: PropTypes.string.isRequired,
            created_at: PropTypes.string.isRequired, // ISO UTC
            status: PropTypes.oneOf(Object.keys(ideaStatus)).isRequired,
        })
    ).isRequired,
    onDeleteIdea: PropTypes.func.isRequired,
};

export default MyIdeas;
