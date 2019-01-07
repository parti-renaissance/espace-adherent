import React from 'react';
import { connect } from 'react-redux';
import { ideaStatus } from '../../constants/api';
import { initContributePage } from '../../redux/thunk/navigation';
import ThreeTabsPage from '../ThreeTabs';
import IdeaCardList from '../../containers/IdeaCardList';

class ContributePage extends React.Component {
    componentDidMount() {
        this.props.initContributePage();
    }

    render() {
        return (
            <ThreeTabsPage
                title="Contribuer aux idées en cours"
                subtitle="Explorez les idées en cours de vos concitoyens et enrichissez-les !"
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
