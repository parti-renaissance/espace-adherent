import React from 'react';
import PropTypes from 'prop-types';

function ReportsModal(props) {
    return (
        <div className="reports-modal">
            <h2 className="reports-modal__title">
				Je lis les {props.reports.length} rapports
            </h2>
            <p className="reports-modal__subtitle">
				Nous n’avons pas attendu l’Atelier des Idées pour vous consulter
            </p>
            {props.reports.map((report, index) => (
                <a
                    className="reports-modal__report"
                    key={index}
                    href={report.file}
                    download
                >
                    <img
                        className="reports-modal__report__pdf-preview"
                        src="/assets/img/pdf_preview.svg"
                    />
                    <div className="reports-modal__report__content">
                        <span className="reports-modal__report__content__file">
                            {report.fileName}
                        </span>
                        <span className="reports-modal__report__content__size">
                            {report.size}
                        </span>
                    </div>
                </a>
            ))}
        </div>
    );
}

ReportsModal.defaultProps = {
    reports: [],
};

ReportsModal.propTypes = {
    reports: PropTypes.arrayOf(
        PropTypes.shape({
            file: PropTypes.string,
            fileName: PropTypes.string,
            size: PropTypes.string,
        })
    ),
};

export default ReportsModal;
