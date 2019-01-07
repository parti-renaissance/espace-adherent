import React from 'react';
import PropTypes from 'prop-types';
import Select from '../Select';

const filterItems = {
    order: {
        options: [
            { value: 'created_at/DESC', label: 'Plus récentes' },
            { value: 'created_at/ASC', label: 'Plus anciennes' },
            { value: 'comments_count/DESC', label: 'Plus commentées' },
            { value: 'votes_count.total/DESC', label: 'Plus votées' },
        ],
    },
};

class IdeaFilters extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            order: null,
            // name: '',
        };
        this.onFilterChange = this.onFilterChange.bind(this);
    }

    onFilterChange(filterKey, value) {
        this.setState({ [filterKey]: value }, () => this.props.onFilterChange(this.state));
    }

    render() {
        return (
            <div className="idea-filters">
                <div className="idea-filters__sort">
                    <p className="idea-filters__label">Trier par</p>
                    <Select
                        options={filterItems.order.options}
                        onSelected={([selected]) => this.onFilterChange('order', selected.value)}
                    />
                </div>
            </div>
        );
    }
}

IdeaFilters.propTypes = {
    onFilterChange: PropTypes.func.isRequired,
};

export default IdeaFilters;
