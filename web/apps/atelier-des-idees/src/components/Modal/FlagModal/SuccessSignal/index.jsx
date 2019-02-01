import React from 'react';
import SuccessModal from '../../SuccessModal';

class SuccessSignal extends React.PureComponent {
    render() {
        return (
            <SuccessModal text="Votre signalement a bien été pris en compte et va être analysé par les équipes de LaREM dans les plus brefs délais." />
        );
    }
}

export default SuccessSignal;
