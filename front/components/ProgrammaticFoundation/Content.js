import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import SearchResults from './SearchResults';
import Approach from './Approach';
import SearchEngine from '../../services/programmatic-foundation/SearchEngine';

export default class Content extends React.Component {
    render() {
        if (this.props.isSearching) {
            return <SearchResults {...(SearchEngine.search(this.props.approaches, this.getFilters()))} />;
        }

        const approaches = this.props.filterIsLeading ?
            this.filterApproachesByIsLeading(this.props.approaches) :
            this.props.approaches;

        return (
            <div className="programmatic-foundation__approaches">
                {approaches.map((approach, index) => <Approach key={index + approach.uuid} approach={approach} />)}
            </div>
        );
    }

    filterApproachesByIsLeading(approaches) {
        return _.filter(_.cloneDeep(approaches), (approach) => {
            const subApproaches = _.filter(approach.subApproaches, (subApproach) => {
                const measures = _.filter(subApproach.measures, (measure) => measure.isLeading);

                if (measures.length) {
                    subApproach.measures = measures;
                }

                return !!measures.length;
            });

            if (subApproaches.length) {
                approach.subApproaches = subApproaches;
            }

            return !!subApproaches.length;
        });
    }

    getFilters() {
        return {
            query: this.props.filterText,
            city: this.props.filterCity,
            isLeading: this.props.filterIsLeading,
        };
    }
}

Content.propTypes = {
    approaches: PropTypes.arrayOf(PropTypes.object).isRequired,
    filterIsLeading: PropTypes.bool,
    isSearching: PropTypes.bool,

    filterText: PropTypes.string,
    filterCity: PropTypes.string,
};

Content.defaultProps = {
    isSearching: false,
    filterIsLeading: false,
    filterText: '',
    filterCity: '',
};
