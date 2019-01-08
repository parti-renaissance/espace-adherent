import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../constants/api';
import Select from '../Select';

const filterItems = {
    order: {
        options: [
            { value: 'created_at/DESC', label: 'Plus récentes' },
            { value: 'created_at/ASC', label: 'Plus anciennes' },
            { value: 'comments_count/DESC', label: 'Plus commentées' },
            { value: 'votes_count.total/DESC', label: 'Plus votées', status: ideaStatus.FINALIZED },
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
        this.formatFilters = this.formatFilters.bind(this);
    }

    onFilterChange(filterKey, value) {
        this.setState({ [filterKey]: value }, () => this.props.onFilterChange(this.formatFilters()));
    }

    formatFilters() {
        return Object.entries(this.state).reduce((acc, [filterName, filterValue]) => {
            const [attr, value] = filterValue.split('/');
            if (value) {
                acc[`${filterName}['${attr}']`] = value;
                return acc;
            }
            acc[filterName] = filterValue;
            return acc;
        }, {});
    }

    render() {
        return (
            <div className="idea-filters">
                <div className="idea-filters__sort">
                    <p className="idea-filters__label">Trier par</p>
                    <Select
                        options={filterItems.order.options.filter(
                            option => !option.status || (!!option.status && option.status === this.props.status)
                        )}
                        defaultValue={filterItems.order.options[0]}
                        onSelected={([selected]) => this.onFilterChange('order', selected.value)}
                    />
                </div>
            </div>
        );
    }
}

IdeaFilters.defaultProps = {
    status: 'PENDING',
};

IdeaFilters.propTypes = {
    onFilterChange: PropTypes.func.isRequired,
    status: PropTypes.oneOf(Object.keys(ideaStatus)),
};

export default IdeaFilters;
