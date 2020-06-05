import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { Link } from 'react-router-dom';
import { ideaStatus } from '../../../../constants/api';
import icn_toggle_content from './../../../../img/icn_toggle_content-blue-yonder.svg';
import Pagination from '../../../Pagination';

const { DRAFT, PENDING, FINALIZED } = ideaStatus;

class MyIdeas extends React.Component {
    constructor(props) {
        super(props);
        this.CAT_IDEAS_FILTER = [
            {
                showCat: 'showDraft',
                label: 'brouillons',
                empty: 'brouillon',
                ideas: props.ideas[DRAFT].items,
                metadata: props.ideas[DRAFT].metadata,
                status: DRAFT,
                el: React.createRef(),
            },
            {
                showCat: 'showPending',
                label: 'propositions en cours d’élaboration',
                empty: 'proposition en cours d’élaboration',
                ideas: props.ideas[PENDING].items,
                metadata: props.ideas[PENDING].metadata,
                status: PENDING,
                el: React.createRef(),
            },
            {
                showCat: 'showFinalized',
                label: 'propositions finalisées',
                empty: 'proposition finalisée',
                ideas: props.ideas[FINALIZED].items,
                metadata: props.ideas[FINALIZED].metadata,
                status: FINALIZED,
                el: React.createRef(),
            },
        ];

        this.state = {
            showDraft: true,
            showPending: true,
            showFinalized: true,
        };

        this.paginate = this.paginate.bind(this);
    }

    paginate(page, status, ref) {
        ref.current.scrollIntoView(true);
        this.props.getMyIdeas({ page, status });
    }

    render() {
        return (
            <div className="my-ideas">
                {this.CAT_IDEAS_FILTER.map((cat, i) => {
                    const {
                        metadata: { current_page: page, total_items: total },
                        status,
                    } = cat;
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
                                alt="Toggle"
                            />
                        </button>
                    ) : (
                        <p className="my-ideas__category__button__label">{cat.label.toUpperCase()}</p>
                    );
                    return (
                        <div className="my-ideas__category" key={i} ref={cat.el}>
                            {categoryHeader}
                            {cat.ideas.length ? (
                                cat.ideas.map(
                                    (idea, j) =>
                                        this.state[cat.showCat] && (
                                            <div className="my-ideas__category__idea" key={j}>
                                                <p className="my-ideas__category__idea__date">
                          Créée le {new Date(idea.created_at).toLocaleDateString()}
                                                </p>
                                                <h4 className="my-ideas__category__idea__name">
                                                    <Link to={`/atelier-des-idees/proposition/${idea.uuid}?mode=lecture`}>{idea.name}</Link>
                                                </h4>
                                                <div className="my-ideas__category__idea__actions">
                                                    <Link
                                                        to={`/atelier-des-idees/proposition/${idea.uuid}`}
                                                        className="my-ideas__category__idea__actions__edit"
                                                    >
                                                        {'FINALIZED' !== idea.status && 'Editer'}
                                                        {'FINALIZED' === idea.status && 'Voir la proposition'}
                                                    </Link>
                                                    <button
                                                        className="button my-ideas__category__idea__actions__delete"
                                                        onClick={() => this.props.onDeleteIdea(idea.uuid)}
                                                    >
                            Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                        )
                                )
                            ) : (
                                <p className="my-ideas__category__empty-label">{`Vous n'avez pas de ${cat.empty}`}</p>
                            )}
                            {!!cat.ideas.length && this.state[cat.showCat] && (
                                <Pagination
                                    nextPage={() => this.paginate(page + 1, status, cat.el)}
                                    prevPage={() => this.paginate(page - 1, status, cat.el)}
                                    goTo={p => this.paginate(p, status, cat.el)}
                                    total={total}
                                    currentPage={page}
                                    pageSize={5}
                                    pagesToShow={5}
                                />
                            )}
                        </div>
                    );
                })}
            </div>
        );
    }
}

const IDEA_TYPE = PropTypes.shape({
    uuid: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    created_at: PropTypes.string.isRequired, // ISO UTC
    status: PropTypes.oneOf(Object.keys(ideaStatus)).isRequired,
});

MyIdeas.propTypes = {
    ideas: PropTypes.shape({
        [DRAFT]: PropTypes.shape({
            items: PropTypes.arrayOf(IDEA_TYPE),
            metadata: PropTypes.object,
        }),
        [PENDING]: PropTypes.shape({
            items: PropTypes.arrayOf(IDEA_TYPE),
            metadata: PropTypes.object,
        }),
        [FINALIZED]: PropTypes.shape({
            items: PropTypes.arrayOf(IDEA_TYPE),
            metadata: PropTypes.object,
        }),
    }).isRequired,
    onDeleteIdea: PropTypes.func.isRequired,
};

export default MyIdeas;
