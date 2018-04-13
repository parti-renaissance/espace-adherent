import React, { Component} from 'react';
import Summary from './../components/charts/Summary'
import Bars from './../components/charts/Bars'

const AdherentContainer = (props) => {
    return (
        <div className="adherent__ctn">
            <h2 className="ctn__title">Adhérents</h2>
            <div className="adherent__ctn__summary">
                <Summary
                    summaryDescription={`Adhérents Indre et Loire`} //Mettre en variable.
                    womanPercentage= {`${33}%`}
                    manPercentage = {`${67}%`}
                />
                <Summary
                    summaryDescription="Adhérents Total"
                    womanPercentage= {`${33}%`}
                    manPercentage = {`${67}%`}
                />
            </div>
            <div className="adherent__ctn__bars">
                <Bars />
                <Bars />
            </div>
        </div>

    )
}

export default AdherentContainer;
