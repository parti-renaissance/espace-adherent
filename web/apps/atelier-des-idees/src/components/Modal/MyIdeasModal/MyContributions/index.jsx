import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { Link } from 'react-router-dom';
import Pagination from '../../../Pagination';

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

        this.paginate = this.paginate.bind(this);
        this.el = React.createRef();
    }

    paginate(page) {
        this.el.current.scrollIntoView(true);
        this.props.getMyContribs({ page });
    }

    render() {
        const {
            items: ideas,
            metadata: {
                current_page: page,
                total_items: total,
            },
        } = this.props.ideas;
        return (
            <div className="my-contributions" ref={this.el}>
                {ideas.length ? (
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
                {ideas.length ? (
                    ideas.map(
                        (idea, i) =>
                            this.state.showList && (
                                <div className="my-contributions__category__idea" key={i}>
                                    <p className="my-contributions__category__idea__date">
                                        Créée le {new Date(idea.created_at).toLocaleDateString()}
                                    </p>
                                    <h4 className="my-contributions__category__idea__name">{idea.name}</h4>
                                    <div className="my-contributions__category__idea__actions">
                                        <Link
                                            to={`/atelier-des-idees/proposition/${idea.uuid}`}
                                            className="my-contributions__category__idea__actions__see-note"
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
                {!!ideas.length && this.state.showList &&
                    <Pagination
                        nextPage={() => this.paginate(page + 1)}
                        prevPage={() => this.paginate(page - 1)}
                        goTo={p => this.paginate(p)}
                        total={total}
                        currentPage={page}
                        pageSize={5}
                        pagesToShow={5}
                    />
                }
            </div>
        );
    }
}

const IDEA_TYPE = PropTypes.shape({
    uuid: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    created_at: PropTypes.string.isRequired, // ISO UTC
});

MyContributions.propTypes = {
    ideas: PropTypes.shape({
        items: PropTypes.arrayOf(IDEA_TYPE),
        metadata: PropTypes.object,
    }).isRequired,
};

export default MyContributions;
