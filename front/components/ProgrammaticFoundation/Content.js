import React, {PropTypes} from 'react';
import _ from 'lodash';
import SearchResults from './SearchResults';
import Approach from './Approach';
import SearchEngine from '../../services/programmatic-foundation/SearchEngine';

export default class Content extends React.Component {
    render() {
        if (this.props.isSearching) {
            return <SearchResults {...(SearchEngine.search(this.props.approaches, this.getFilters()))} />
        }

        const approaches = this.props.filterIsLeading ?
            this.filterApproachesByIsLeading(this.props.approaches) :
            this.props.approaches;

        return (
            <div className="programmatic-foundation__approaches">
                {approaches.map((approach, index) => {
                    return <Approach key={index+approach.uuid} approach={approach} />;
                })}
            </div>
        );
    }

    filterApproachesByIsLeading(approaches) {
        return _.filter(_.cloneDeep(approaches), (approach) => {
            const subApproaches = _.filter(approach.sub_approaches, (sub_approach) => {
                const measures = _.filter(sub_approach.measures, (measure) => { return measure.isLeading;});

                if (measures.length) {
                    sub_approach.measures = measures;
                }

                return !!measures.length;
            });

            if (subApproaches.length) {
                approach.sub_approaches = subApproaches;
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
