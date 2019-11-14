import React, {PropTypes} from 'react';

export default class Breadcrumbs extends React.Component {
    render() {
        const breadcrumbs = [];

        if (this.props.isSearching) {
            breadcrumbs.push(
                <a href="#" className={"link--no--decor"} onClick={this.props.onExitClick}>
                    ⟵ Quitter la recherche
                </a>
            );
        } else {
            breadcrumbs.push('Socle programme', 'Toutes les mesures');
        }

        return (
            <ul className="programmatic-foundation__breadcrumb text--body">
                {breadcrumbs.map((item, index) => <li key={index}>{item}</li>)}
            </ul>
        );
    }
}

Breadcrumbs.propTypes = {
    onExitClick: PropTypes.func.isRequired,
    isSearching: PropTypes.bool,
};

Breadcrumbs.defaultProps = {
    isSearching: false,
};
