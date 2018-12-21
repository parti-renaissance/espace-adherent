import React from 'react';

import { REPORTS_MODAL } from '../../constants/modalTypes';

// TODO: api call reports
const mockReports = {
    reports: [
        {
            file: '/',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
        {
            file: '/',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
        {
            file: '/',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
        {
            file: '/',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
        {
            file: '/',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
    ],
};
class Reports extends React.PureComponent {
    render() {
        return (
            <div className="l__wrapper reports">
                <div className="reports__first-section">
                    <h3 className="reports__first-section__title">
						Nous n’avons pas attendu l’Atelier des Idées pour vous consulter
                    </h3>
                    <p className="reports__first-section__text">
						Consultez les rapports de consultations terminées
                    </p>
                    <button
                        className="reports__first-section__button button button--primary"
                        onClick={() => this.props.showModal(REPORTS_MODAL)}
                    >
						Je lis les rapports
                    </button>
                </div>
                <div className="reports__second-section">
                    {/* TODO: replace by image */}
                    <div className="form" />
                </div>
            </div>
        );
    }
}

export default Reports;
