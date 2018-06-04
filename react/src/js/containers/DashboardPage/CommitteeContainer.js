import React, { Component } from 'react';
import PropTypes from 'prop-types';

import Summary from './../../components/charts/Summary';
import Ranking from './../../components/charts/Ranking';
import SelectCustom from './../../components/modules/SelectCustom';
import { getPercentage } from '../../utils/math';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

class CommitteeContainer extends Component {
    constructor(props) {
        super(props);
        this.handleChange = this.handleChange.bind(this);
        this.state = { input: [] };
    }

    handleChange = (input) => {
        this.setState({ input });
    };

    render() {
        const { graphData, onSelect, committeesFive, summaryTotal, filteredItem, setFilteredItem } = this.props;

        const committeesMale = getPercentage(summaryTotal.supervisors.male, summaryTotal.supervisors.total);
        const committeesFemale = getPercentage(summaryTotal.supervisors.female, summaryTotal.supervisors.total);
        const supervisorsMale = getPercentage(summaryTotal.supervisors.male, summaryTotal.supervisors.total);
        const supervirosFemale = getPercentage(summaryTotal.supervisors.female, summaryTotal.supervisors.total);

        return (
            <div className="committee__ctn">
                <h2 className="ctn__title">Comités</h2>
                <div className="committee__ctn__summary">
                    <Summary
                        summaryTotal={summaryTotal.committees}
                        summaryDescription={'Comités créés'}
                        womanPercentage={committeesFemale}
                        manPercentage={committeesMale}
                    />
                    <Summary
                        summaryTotal={summaryTotal.members.total}
                        summaryDescription={'Inscrits dans un comité'}
                        womanPercentage={supervirosFemale}
                        manPercentage={supervisorsMale}
                    />
                </div>
                <div className="committee__ctn__ranking">
                    <Ranking committees={committeesFive.most_active} title={'Comites les plus actifs'} />
                    <Ranking committees={committeesFive.least_active} title={'Comites les moins actifs'} />
                </div>
                <div className="committee__ctn__select">
                    <SelectCustom
                        onSelect={onSelect}
                        onFilter={setFilteredItem}
                        autocompleteSearch={this.props.autocompleteSearch}
                        committeeFilter={this.props.committeeFilter}
                        autocomplete={this.props.autocomplete}
                        autocompletePending={this.props.autocompletePending}
                        id={'selectCommittee'}
                        name={'selectCommittee'}
                    />
                </div>
                <div className="committee__ctn__bars">
                    <ResponsiveContainer>
                        <BarChart
                            width={600}
                            height={400}
                            data={graphData}
                            margin={{
                                top: 50,
                                right: 30,
                                left: 20,
                                bottom: 5,
                            }}
                        >
                            <CartesianGrid stroke={'#FEF2F2'} vertical={false} />
                            <XAxis dataKey="date" stroke="" />
                            <YAxis stroke={'#FEF2F2'} />
                            <Tooltip
                                cursor={{
                                    stroke: '#FEF0F0',
                                    fill: '#FFF',
                                }}
                                itemStyle={{
                                    textAlign: 'left',
                                    stroke: '#FEF0F0',
                                }}
                            />
                            <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                            <Bar
                                name={`Adhérents ${filteredItem}`}
                                dataKey={'count'}
                                fill={'#F8BCBC'}
                                barSize={10}
                                animationEasing={'ease-in-out'}
                            />
                        </BarChart>
                    </ResponsiveContainer>
                    <ResponsiveContainer>
                        <BarChart
                            width={600}
                            height={400}
                            data={graphData}
                            margin={{
                                top: 50,
                                right: 30,
                                left: 20,
                                bottom: 5,
                            }}
                        >
                            <CartesianGrid stroke={'#FEF2F2'} vertical={false} />
                            <XAxis dataKey="date" stroke="" />
                            <YAxis stroke={'#FEF2F2'} />
                            <Tooltip
                                cursor={{
                                    stroke: '#FEF0F0',
                                    fill: '#FFF',
                                }}
                                itemStyle={{
                                    textAlign: 'left',
                                    stroke: '#FEF0F0',
                                }}
                            />
                            <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                            <Bar
                                name={`Membres comités locaux ${filteredItem}`}
                                dataKey={'count'}
                                fill={'#6BA0EE'}
                                barSize={10}
                                animationEasing={'ease-in-out'}
                            />
                            <Bar
                                name={`Participants aux événements ${filteredItem}`}
                                dataKey={'count'}
                                fill={'#F8BCBC'}
                                barSize={10}
                                animationEasing={'ease-in-out'}
                            />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            </div>
        );
    }
}

export default CommitteeContainer;

CommitteeContainer.propTypes = {
    committees: PropTypes.array,
    title: PropTypes.string,
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
    dataKey: PropTypes.object,
    cursor: PropTypes.object,
    itemStyle: PropTypes.object,
    name: PropTypes.string,
    fill: PropTypes.string,
    barSize: PropTypes.number,
    animationEasing: PropTypes.string,
};
