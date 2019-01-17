import React from 'react';
import { ideaStatus } from '../../constants/api';
import ThreeTabsPage from '../ThreeTabs';
import IdeaCardList from '../../containers/IdeaCardList';

class ConsultPage extends React.PureComponent {
    componentDidMount() {
        window.scrollTo(0, 0);
    }

    render() {
        return (
            <ThreeTabsPage
                title="Les propositions finalisées"
                subtitle="Donnez votre avis sur les propositions des marcheurs"
            >
                <div className="consult-page">
                    <div className="l__wrapper">
                        <IdeaCardList mode="grid" status={ideaStatus.FINALIZED} withPaging={true} />
                    </div>
                </div>
            </ThreeTabsPage>
        );
    }
}

export default ConsultPage;
