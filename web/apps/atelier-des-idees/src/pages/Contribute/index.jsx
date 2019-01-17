import React from 'react';
import { connect } from 'react-redux';
import { ideaStatus } from '../../constants/api';
import { initContributePage } from '../../redux/thunk/navigation';
import ThreeTabsPage from '../ThreeTabs';
import IdeaCardList from '../../containers/IdeaCardList';

class ContributePage extends React.Component {
    componentDidMount() {
        window.scrollTo(0, 0);
        this.props.initContributePage();
    }

    render() {
        return (
            <ThreeTabsPage
                title="Les propositions en cours"
                subtitle="Contribuez aux propositions des marcheurs en cours d'élaboration"
            >
                <div className="contribute-page">
                    <div className="l__wrapper">
                        <IdeaCardList mode="grid" status={ideaStatus.PENDING} withPaging={true} />
                    </div>
                </div>
            </ThreeTabsPage>
        );
    }
}

export default connect(
    null,
    { initContributePage }
)(ContributePage);
