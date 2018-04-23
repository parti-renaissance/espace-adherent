import React, { Component } from 'react';
import Summary from './../components/charts/Summary';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { connect } from 'react-redux';
import * as actionCreators from './../actions/index.js';
import data from './../fakeData/data';

class EventContainer extends Component {
    render() {
        return (
            <div className="event__ctn">
                <h2 className="ctn__title">Evénements</h2>
                <div className="event__ctn__summary">
                    <Summary summaryTotal={716} summaryDescription="Evénéments Indre et Loire" />{' '}
                    {/* Mettre en variable.*/}
                    <Summary summaryTotal={760} summaryDescription="inscrits dans un événement" />
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
                                fill="#F8BCBC"
                                barSize={10}
                                animationEasing="ease-in-out"
                            />
                            <Bar
                                name="Evénement Député"
                                dataKey="adherentMembre"
                                fill="#F8BCBC"
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
                            <Bar
                                name="Total inscrit à un événement"
                                dataKey="adherent"
                                fill="#6BA0EE"
                                barSize={10}
                                stroke-linecap="round"
                            />
                            <Bar
                                name="Adhérent inscrits à un événement"
                                dataKey="adherentMembre"
                                fill="#F8BCBC"
                                barSize={10}
                                offsetRadius={10}
                            />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            </div>
        );
    }
}

export default EventContainer;
