import Alpine from 'alpinejs';
import Tooltip from '../components/Tooltip';
import Validator from '../components/Validator';
import ReIcon from '../components/ReIcon';
import ReSelect from '../components/ReSelect';

window.Alpine = Alpine;
export default () => {
    Alpine.directive('tooltip', Tooltip);

    Alpine.data('xValidateField', Validator.xValidate);
    Alpine.data('xReIcon', ReIcon.xReIcon);
    Alpine.data('xReSelect', ReSelect.xReSelect);
};
