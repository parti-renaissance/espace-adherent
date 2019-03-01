import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { Link } from 'react-router-dom';
import { ideaStatus } from './../../constants/api';
import icn_20px_contributors from './../../img/icn_20px_contributors.svg';
import icn_20px_comments from './../../img/icn_20px_comments.svg';
import icn_20px_link_to from './../../img/icn_20px_link_to.svg';
import greenCheckIcn from './../../img/icn_checklist.svg';

import { keyPressed } from './../../helpers/navigation';

function conjugation(nb) {
    if (0 === nb) {
        return;
    } else if (1 === nb) {
        return 'vote';
    }
    return 'votes';
}

function AutoComplete(props) {
    const inputValue = props.value;
    document.onkeydown = keyPressed;
    const optionsLength = props.options && props.options.items.length;

    return (
        <div className="autocomplete">
            <ul className="autocomplete__wrapper">
                {props.options && 0 !== props.options.metadata.count && (
                    <div className="autocomplete__help">
                        <p>
							Vous n'êtes pas seuls ! Il y a{' '}
                            <strong>
                                {optionsLength} proposition{1 < optionsLength ? 's' : ''}
                            </strong>{' '}
							qui pouraient vous intéresser !
                        </p>
                    </div>
                )}
                {props.options &&
					props.options.items.map((items, i) => (
					    <Link
					        to={`/atelier-des-idees/proposition/${items.uuid}`}
					        className="idea-card__link"
					        key={items.uuid}
					        data-selectlist="true"
					        data-prev={`select${i}`}
					        data-next={i + 1 === optionsLength ? 'lastItem' : `select${i + 2}`}
					        id={`select${i + 1}`}
					    >
					        <li>
					            <div className="autocomplete__name">
					                <p>{items.name}</p>
					                <img src={icn_20px_link_to} alt="Lien vers l'idée" />
					            </div>
					            <div className="autocomplete__numbers">
					                {items.status === ideaStatus.FINALIZED ? (
					                    <span>
					                        {0 !== items.votes_count.total ? (
					                            <span>
					                                {items.votes_count.total} {conjugation(items.votes_count.total)}
					                            </span>
					                        ) : (
					                            <small>Soyez le premier à voter pour cette proposition !</small>
					                        )}
					                    </span>
					                ) : (
					                    <React.Fragment>
					                        <span>
					                            <img src={icn_20px_contributors} alt="Contributeurs" />
					                            <p>{items.contributors_count}</p>
					                        </span>
					                        <span>
					                            <img src={icn_20px_comments} alt="Commentaires" />{' '}
					                            <p>{items.comments_count}</p>
					                        </span>
					                    </React.Fragment>
					                )}
					            </div>
					        </li>
					    </Link>
					))}
            </ul>
        </div>
    );
}

AutoComplete.defaultProps = {};

AutoComplete.propTypes = {};

export default AutoComplete;
