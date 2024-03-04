import FirstFormStep from './components/FirstFormStep';
import SecondFormStep, { isFranceCountry } from './components/SecondFormStep';
import ThirdFormStep from './components/ThirdFormStep';
import Page from './components/Page';

export default () => {
    window.isFranceCountry = isFranceCountry;
    window.Alpine.data('FirstFormStep', FirstFormStep);
    window.Alpine.data('SecondFormStep', SecondFormStep);
    window.Alpine.data('ThirdFormStep', ThirdFormStep);
    window.Alpine.data('xFunnelPage', Page);
};
