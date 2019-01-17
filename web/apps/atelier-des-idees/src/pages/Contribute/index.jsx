import React from 'react';
import { ideaStatus } from '../../constants/api';
import ThreeTabsPage from '../ThreeTabs';
import IdeaCardList from '../../containers/IdeaCardList';

class ContributePage extends React.PureComponent {
    componentDidMount() {
        window.scrollTo(0, 0);
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

export default ContributePage;
