import React, { Component } from 'react';
import Summary from './../components/charts/Summary';
import Timer from './../components/Timer';
// import { animateNb } from './../utils';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import data from './../fakeData/data';
import { connect } from 'react-redux';
import * as actionCreators from './../actions/index.js';

class AdherentContainer extends Component {
    render() {
        return (
            <div className="adherent__ctn">
                <h2 className="ctn__title">Adhérents</h2>
                <h2 className="dashboard__title">
					Résultats à
                    <span className="text--blue--primary">
                        <Timer />
                    </span>
                </h2>
                <div className="adherent__ctn__summary">
                    <Summary
                        summaryTotal={98756}
                        summaryDescription={'Adhérents Indre et Loire'} // Mettre en variable.
                        womanPercentage={`${33}%`}
                        manPercentage={`${67}%`}
                    />
                    <Summary
                        summaryTotal={400987}
                        summaryDescription="Adhérents Total"
                        womanPercentage={`${33}%`}
                        manPercentage={`${67}%`}
                    />
                </div>
                <div className="adherent__ctn__bars">
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
                                name="Adhérent"
                                dataKey="adherent"
                                fill="#6BA0EE"
                                barSize={10}
                                animationEasing="ease-in-out"
                            />
                            <Bar
                                name="Adhérent membre d'un comité"
                                dataKey="adherentMembre"
                                fill="#F8BCBC"
                                barSize={10}
                                offsetRadius={10}
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
                                name="Recevant des e-mails (référents)"
                                dataKey="adherent"
                                fill="#6BA0EE"
                                barSize={10}
                                stroke-linecap="round"
                            />
                            <Bar
                                name="Recevant des e-mails de leur(s) comité(s)"
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

export default AdherentContainer;
