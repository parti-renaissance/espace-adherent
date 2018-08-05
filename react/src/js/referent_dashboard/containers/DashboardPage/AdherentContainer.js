import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Summary from './../../components/charts/Summary';
import { getPercentage } from './../../../utils/math';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

class AdherentContainer extends Component {
    render() {
        const { graphData, summaryTotal } = this.props;

        const areaMale = getPercentage(summaryTotal.area.male, summaryTotal.area.total);
        const areaFemale = getPercentage(summaryTotal.area.female, summaryTotal.area.total);
        const totalMale = getPercentage(summaryTotal.total.male, summaryTotal.total.total);
        const totalFemale = getPercentage(summaryTotal.total.female, summaryTotal.total.total);

        return (
            <div className="adherent__ctn">
                <h2 className="ctn__title">Adhérents</h2>
                <div className="adherent__ctn__summary">
                    <Summary
                        summaryTotal={summaryTotal.area.total}
                        summaryDescription={'Adhérents dans ma zone'}
                        womanPercentage={`${areaFemale}`}
                        manPercentage={`${areaMale}`}
                    />
                    <Summary
                        summaryTotal={summaryTotal.total.total}
                        summaryDescription="Adhérents Total"
                        womanPercentage={`${totalFemale}`}
                        manPercentage={`${totalMale}`}
                    />
                </div>
                <div className="adherent__ctn__bars">
                    <ResponsiveContainer>
                        <BarChart
                            width={600}
                            height={400}
                            data={graphData}
                            margin={{ top: 50, right: 30, left: 20, bottom: 5 }}>
                            <CartesianGrid stroke="#FEF2F2" vertical={false} />
                            <XAxis dataKey="date" stroke="" />
                            <YAxis stroke="#FEF2F2" />
                            <Tooltip
                                cursor={{ stroke: '#FEF0F0', fill: '#FFF' }}
                                itemStyle={{
                                    textAlign: 'left',
                                    stroke: '#FEF0F0',
                                }}
                            />
                            <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                            <Bar
                                name="Adhérent"
                                dataKey="total"
                                fill="#6BA0EE"
                                barSize={10}
                                animationEasing="ease-in-out"
                            />
                            <Bar
                                name="Adhérent membre d'un comité"
                                dataKey="count"
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
                            data={graphData}
                            margin={{ top: 50, right: 30, left: 20, bottom: 5 }}>
                            <CartesianGrid stroke="#FEF2F2" vertical={false} />
                            <XAxis dataKey="date" stroke="" />
                            <YAxis stroke="#FEF2F2" />
                            <Tooltip
                                cursor={{ stroke: '#FEF0F0', fill: '#FFF' }}
                                itemStyle={{
                                    textAlign: 'left',
                                    stroke: '#FEF0F0',
                                }}
                            />
                            <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                            <Bar
                                name="Recevant des e-mails (référents)"
                                dataKey="subscribed_emails_referents"
                                fill="#6BA0EE"
                                barSize={10}
                            />
                            <Bar
                                name="Recevant des e-mails de leur(s) comité(s)"
                                dataKey="subscribed_emails_local_host"
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

export default AdherentContainer;

AdherentContainer.propTypes = {
    summaryTotal: PropTypes.object,
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
