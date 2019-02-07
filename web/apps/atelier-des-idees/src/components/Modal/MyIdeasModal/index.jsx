import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../../constants/api';
import Tabs from '../../Tabs';
import MyIdeas from './MyIdeas';
import MyContributions from './MyContributions';

function MyIdeasModal(props) {
    const panes = [
        {
            title: 'Mes propositions',
            component: () => <MyIdeas ideas={props.my_ideas} onDeleteIdea={props.onDeleteIdea} />,
        },
        {
            title: 'Mes contributions',
            component: () => <MyContributions ideas={props.my_contribs} />,
        },
    ];

    return (
        <div className="my-ideas-modal">
            <h2 className="my-ideas-modal__title">Mes propositions et contributions</h2>
            <p className="my-ideas-modal__subtitle">
				Retrouvez ici toutes vos propositions et celles auxquelles vous avez contribué.
            </p>
            <Tabs panes={panes} defaultActiveKey={'my_ideas' === props.tabActive ? '0' : '1'} />
        </div>
    );
}

MyIdeasModal.defaultProps = {
    tabActive: 'my_ideas',
};

MyIdeasModal.propTypes = {
    tabActive: PropTypes.oneOf(['my_ideas', 'my_contributions']),
    my_contribs: PropTypes.arrayOf(
        PropTypes.shape({
            uuid: PropTypes.string.isRequired,
            name: PropTypes.string.isRequired,
            created_at: PropTypes.string.isRequired, // ISO UTC
        })
    ).isRequired,
    my_ideas: PropTypes.shape({
        [ideaStatus.DRAFT]: PropTypes.object,
        [ideaStatus.PENDING]: PropTypes.object,
        [ideaStatus.FINALIZED]: PropTypes.object,
    }).isRequired,
    onDeleteIdea: PropTypes.func.isRequired,
};

export default MyIdeasModal;
