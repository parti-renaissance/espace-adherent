import React, { Component } from 'react';
import Summary from './../components/charts/Summary';
import Bars from './../components/charts/Bars';
import Ranking from './../components/charts/Ranking';
import Select from './../components/modules/Select';


class CommitteeContainer extends Component {
    render() {
        return (
            <div className="committee__ctn">
                <h2 className="ctn__title">Comités</h2>
                <div className="committee__ctn__summary">
                    <Summary
                        summaryDescription="Comités créés"
                    />
                    <Summary
                        summaryDescription="Inscrits dans un comité"
                        womanPercentage= {`${33}%`}
                        manPercentage = {`${67}%`}
                    />
                </div>
                <div className="committee__ctn__ranking">
                    <Ranking />
                    <Ranking />
                </div>
                <div className="committee__ctn__select">
                    <Select />
                    <div></div>
                </div>
                <div className="committee__ctn__bars">
                    <Bars />
                    <Bars />
                </div>
            </div>
        )
    }
};

export default CommitteeContainer;
