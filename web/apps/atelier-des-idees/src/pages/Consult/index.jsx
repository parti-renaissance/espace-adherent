import React from 'react';
import { connect } from 'react-redux';
import { ideaStatus } from '../../constants/api';
import { initConsultPage } from '../../redux/thunk/navigation';
import ThreeTabsPage from '../ThreeTabs';
import IdeaCardList from '../../containers/IdeaCardList';

class ConsultPage extends React.Component {
    componentDidMount() {
        this.props.initConsultPage();
    }

    render() {
        return (
            <ThreeTabsPage
                title="Les idées finalisées"
                subtitle="Consultez les idées devenues de vraies propositions !"
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

export default connect(
    null,
    { initConsultPage }
)(ConsultPage);
