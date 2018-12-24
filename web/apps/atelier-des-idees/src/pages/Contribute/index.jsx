import React from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../constants/api';
import { initContributePage } from '../../redux/thunk/navigation';
import IdeaCardList from '../../containers/IdeaCardList';

class ContributePage extends React.Component {
    componentDidMount() {
        this.props.initContributePage();
    }

    render() {
        return (
            <div className="contribute-page">
                <div className="l__wrapper">
                    <IdeaCardList mode="grid" status={ideaStatus.FINALIZED} withPaging={true} />
                </div>
            </div>
        );
    }
}

export default connect(
    null,
    { initContributePage }
)(ContributePage);
