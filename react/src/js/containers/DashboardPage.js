import React, { Component } from 'react';

import AdherentContainer from './AdherentContainer';
import CommitteeContainer from './CommitteeContainer';
import EventContainer from './EventContainer';
import Header from './../components/Header';
import Nav from './../components/Nav';

class DashboardPage extends Component {
    render() {
        return (
            <div className="dashboard__ctn">
                <Header
                    name="Mickaël-Ange"
                    departmentName="Indre-et-Loire"
                />
                <Nav />
                <div className="wrapper">
                    <AdherentContainer />
                    <CommitteeContainer />
                    <EventContainer />
                </div>

            </div>

        )
    }

}

export default DashboardPage;
