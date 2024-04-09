import FirstFormStep from './components/FirstFormStep';
import SecondFormStep, { isFranceCountry } from './components/SecondFormStep';
import Page from './components/Page';
import EmailVerificationForm from './components/EmailVerificationForm';

export default () => {
    window.isFranceCountry = isFranceCountry;
    window.Alpine.data('FirstFormStep', FirstFormStep);
    window.Alpine.data('SecondFormStep', SecondFormStep);
    window.Alpine.data('xFunnelPage', Page);
    window.Alpine.data('xEmailVerificationForm', EmailVerificationForm);
};
