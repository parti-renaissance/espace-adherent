import React, { Component } from 'react';
import PropTypes from 'prop-types';

import Summary from './../components/charts/Summary';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

import data from './../fakeData/data';

class EventContainer extends Component {
    render() {
        const { summaryTotal } = this.props;
        return (
            <div className="event__ctn">
                <h2 className="ctn__title">Evénements</h2>
                <div className="event__ctn__summary">
                    <Summary summaryTotal={summaryTotal} summaryDescription="Evénéments Indre et Loire" />
                    <Summary summaryTotal={summaryTotal} summaryDescription="inscrits dans un événement" />
                </div>
                <div className="event__ctn__bars">
                    <ResponsiveContainer>
                        <BarChart
                            width={600}
                            height={400}
                            data={data}
                            margin={{ top: 50, right: 30, left: 20, bottom: 5 }}>
                            <CartesianGrid stroke="#FEF2F2" vertical={false} />
                            <XAxis dataKey="name" stroke="" />
                            <YAxis stroke="#FEF2F2" />
                            <Tooltip
                                cursor={{ stroke: '#FEF0F0', fill: '#FFF' }}
                                itemStyle={{ textAlign: 'left', stroke: '#FEF0F0' }}
                            />
                            <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                            <Bar
                                name="Evénement CL"
                                dataKey="adherent"
                                fill="#6BA0EE"
                                barSize={10}
                                animationEasing="ease-in-out"
                            />
                            <Bar
                                name="Evénement PC"
                                dataKey="adherentMembre"
                                fill="#F8BCBC"
                                barSize={10}
                                animationEasing="ease-in-out"
                            />
                            <Bar
                                name="Evénement Ref"
                                dataKey="adherentMembre"
                                fill={'#ff4dc299'}
                                barSize={10}
                                animationEasing="ease-in-out"
                            />
                            <Bar
                                name="Evénement Député"
                                dataKey="adherentMembre"
                                fill={'#7064dc'}
                                barSize={10}
                                animationEasing="ease-in-out"
                            />
                        </BarChart>
                    </ResponsiveContainer>
                    <ResponsiveContainer>
                        <BarChart
                            width={600}
                            height={400}
                            data={data}
                            margin={{ top: 50, right: 30, left: 20, bottom: 5 }}>
                            <CartesianGrid stroke="#FEF2F2" vertical={false} />
                            <XAxis dataKey="name" stroke="" />
                            <YAxis stroke="#FEF2F2" />
                            <Tooltip
                                cursor={{ stroke: '#FEF0F0', fill: '#FFF' }}
                                itemStyle={{ textAlign: 'left', stroke: '#FEF0F0' }}
                            />
                            <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                            <Bar name="Total inscrit à un événement" dataKey="adherent" fill="#6BA0EE" barSize={10} />
                            <Bar
                                name="Adhérent inscrits à un événement"
                                dataKey="adherentMembre"
                                fill="#F8BCBC"
                                barSize={10}
                            />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            </div>
        );
    }
}

export default EventContainer;

EventContainer.propTypes = {
    committees: PropTypes.array,
    rankingTitle: PropTypes.string,
    summaryTotal: PropTypes.string,
    summaryDescription: PropTypes.string,
    womanPercentage: PropTypes.number,
    manPercentage: PropTypes.number,
    width: PropTypes.number,
    height: PropTypes.number,
    data: PropTypes.array,
    margin: PropTypes.object,
    stroke: PropTypes.string,
    vertical: PropTypes.bool,
    dataKey: PropTypes.string,
    cursor: PropTypes.object,
    itemStyle: PropTypes.object,
    name: PropTypes.string,
    fill: PropTypes.string,
    barSize: PropTypes.number,
    animationEasing: PropTypes.string,
};
