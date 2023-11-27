import Alpine from 'alpinejs';
import Tooltip from '../components/Tooltip';
import Validator from '../components/Validator';
import ReIcon from '../components/ReIcon';

window.Alpine = Alpine;
Alpine.data();
export default () => {
    Alpine.directive('tooltip', Tooltip);
    document.addEventListener('alpine:init', () => {
        Alpine.data('xValidateField', Validator.xValidate);
        Alpine.data('xReIcon', ReIcon.xReIcon);
    });
    Alpine.start();
};
