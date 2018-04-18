import React, { Component } from 'react';
import Summary from './../components/charts/Summary';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend } from 'recharts';
import data from './../fakeData/data';

const AdherentContainer = props => (
    <div className="adherent__ctn">
        <h2 className="ctn__title">Adhérents</h2>
        <div className="adherent__ctn__summary">
            <Summary
                summaryTotal={9876}
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
            <BarChart width={600} height={400} data={data} margin={{ top: 50, right: 30, left: 20, bottom: 5 }}>
                <CartesianGrid stroke="#FEF2F2" vertical={false} />
                <XAxis dataKey="name" stroke="" />
                <YAxis stroke="#FEF2F2" />
                <Tooltip
                    cursor={{ stroke: '#FEF0F0', fill: '#FFF' }}
                    itemStyle={{ textAlign: 'left', stroke: '#FEF0F0' }}
                />
                <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                <Bar name="Adhérent" dataKey="adherent" fill="#6BA0EE" barSize={10} animationEasing="ease-in-out" />
                <Bar
                    name="Adhérent membre d'un comité"
                    dataKey="adherentMembre"
                    fill="#F8BCBC"
                    barSize={10}
                    offsetRadius={10}
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
        </div>
    </div>
);

export default AdherentContainer;
