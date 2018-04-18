import React, { Component } from 'react';
import Summary from './../components/charts/Summary';
import Ranking from './../components/charts/Ranking';
import Select from './../components/modules/Select';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend } from 'recharts';
import data from './../fakeData/data';

class CommitteeContainer extends Component {
    render() {
        return (
            <div className="committee__ctn">
                <h2 className="ctn__title">Comités</h2>
                <div className="committee__ctn__summary">
                    <Summary summaryTotal={876} summaryDescription="Comités créés" />
                    <Summary
                        summaryTotal={76}
                        summaryDescription="Inscrits dans un comité"
                        womanPercentage={`${33}%`}
                        manPercentage={`${67}%`}
                    />
                </div>

                <div className="committee__ctn__ranking">
                    <Ranking rankingTitle="Comites les plus actifs" />
                    <Ranking rankingTitle="Comites les moins actifs" />
                </div>

                <div className="committee__ctn__select">
                    <Select />
                </div>

                <div className="committee__ctn__bars">
                    <BarChart width={600} height={400} data={data} margin={{ top: 50, right: 30, left: 20, bottom: 5 }}>
                        <CartesianGrid stroke="#FEF2F2" vertical={false} />
                        <XAxis dataKey="name" stroke="" />
                        <YAxis stroke="#FEF2F2" />
                        <Tooltip
                            cursor={{ stroke: '#FEF0F0', fill: '#FFF' }}
                            itemStyle={{ textAlign: 'left', stroke: '#FEF0F0' }}
                        />
                        <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                        <Bar
                            name="Evénements"
                            dataKey="adherent"
                            fill="#F8BCBC"
                            barSize={10}
                            stroke-linecap="round"
                            animationEasing="ease-in-out"
                        />
                    </BarChart>

                    <BarChart width={600} height={400} data={data} margin={{ top: 50, right: 30, left: 20, bottom: 5 }}>
                        <CartesianGrid stroke="#FEF2F2" vertical={false} />
                        <XAxis dataKey="name" stroke="" />
                        <YAxis stroke="#FEF2F2" />
                        <Tooltip
                            cursor={{ stroke: '#FEF0F0', fill: '#FFF' }}
                            itemStyle={{ textAlign: 'left', stroke: '#FEF0F0' }}
                        />
                        <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                        <Bar
                            name="Membres comités locaux"
                            dataKey="adherent"
                            fill="#6BA0EE"
                            barSize={10}
                            animationEasing="ease-in-out"
                        />
                        <Bar
                            name="Participants aux événements"
                            dataKey="adherentMembre"
                            fill="#F8BCBC"
                            barSize={10}
                            offsetRadius={10}
                            animationEasing="ease-in-out"
                        />
                    </BarChart>
                </div>
            </div>
        );
    }
}

export default CommitteeContainer;
