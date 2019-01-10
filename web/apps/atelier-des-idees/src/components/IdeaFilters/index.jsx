import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus, AUTHOR_CATEGORIES } from '../../constants/api';
import Select from '../Select';

class IdeaFilters extends React.Component {
    constructor(props) {
        super(props);
        this.filterItems = {
            order: {
                options: [
                    { value: 'order[\'publishedAt\']/DESC', label: 'Plus récentes' },
                    { value: 'order[\'publishedAt\']/ASC', label: 'Plus anciennes' },
                    { value: 'commentsCount/DESC', label: 'Plus commentées' },
                    { value: 'votesCount/DESC', label: 'Plus votées', status: ideaStatus.FINALIZED },
                ],
            },
            author_category: {
                options: Object.entries(AUTHOR_CATEGORIES).map(([key, value]) => ({ label: value, value: key })),
            },
        };
        this.state = {
            order: this.filterItems.order.options[0].value,
            author_category: null,
            // name: '',
        };
        // bindings
        this.onFilterChange = this.onFilterChange.bind(this);
        this.formatFilters = this.formatFilters.bind(this);
    }

    onFilterChange(filterKey, value) {
        this.setState({ [filterKey]: value }, () => this.props.onFilterChange(this.formatFilters()));
    }

    formatFilters() {
        const { name, ...filters } = this.state;
        const formattedFilters = Object.entries(filters).reduce((acc, [filterName, filterValue]) => {
            if (filterValue) {
                const [attr, value] = filterValue.split('/');
                if (value) {
                    acc[attr] = value;
                    return acc;
                }
                acc[filterName] = filterValue;
            }
            return acc;
        }, {});
        return { name, ...formattedFilters };
    }

    render() {
        return (
            <div className="idea-filters">
                <div className="idea-filters__section idea-filters__filter">
                    <p className="idea-filters__label">Filtrer par</p>
                    {/* <input
                        className="idea-filters__input"
                        value={this.state.name}
                        onChange={e => this.setState({ name: e.target.value })}
                        placeholder="Mot clé"
                    />*/}
                    <Select
                        options={this.filterItems.author_category.options}
                        placeholder="Auteur"
                        onSelected={([selected]) => this.onFilterChange('author_category', selected.value)}
                    />
                    {!!this.props.options && !!this.props.options.themes.length && (
                        <Select
                            options={this.props.options.themes}
                            placeholder="Thème"
                            onSelected={([selected]) => this.onFilterChange('theme.name', selected.value)}
                        />
                    )}
                </div>
                <div className="idea-filters__section idea-filters__sort">
                    <p className="idea-filters__label">Trier par</p>
                    <Select
                        options={this.filterItems.order.options.filter(
                            option => !option.status || (!!option.status && option.status === this.props.status)
                        )}
                        defaultValue={this.filterItems.order.options[0]}
                        onSelected={([selected]) => this.onFilterChange('order', selected.value)}
                    />
                </div>
            </div>
        );
    }
}

IdeaFilters.defaultProps = {
    status: 'PENDING',
    options: undefined,
};

IdeaFilters.propTypes = {
    onFilterChange: PropTypes.func.isRequired,
    status: PropTypes.oneOf(Object.keys(ideaStatus)),
    options: PropTypes.shape({
        themes: PropTypes.array,
        categories: PropTypes.array,
        needs: PropTypes.array,
    }),
};

export default IdeaFilters;
