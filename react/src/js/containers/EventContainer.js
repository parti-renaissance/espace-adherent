import React, { Component } from 'react';
import Summary from './../components/charts/Summary';
import Bars from './../components/charts/Bars';

class EventContainer extends Component {
    render() {
        return (
            <div className="event__ctn">
                <h2 className="ctn__title">Evénements</h2>
                <div className="event__ctn__summary">
                    <Summary
                        summaryDescription="Evénéments Indre et Loire"/> {/*Mettre en variable.*/}
                    <Summary
                        summaryDescription="inscrits dans un événement"/>
                </div>
                <div className="event__ctn__bars">
                    <Bars />
                    <Bars />
                </div>
            </div>
        )
    }
};

export default EventContainer;
