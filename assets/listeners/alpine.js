import Alpine from 'alpinejs';
import Tooltip from '../components/Tooltip';
import NumberOnly from '../components/InputNumberOnly';
import Validator from '../components/Validator';
import ReIcon from '../components/ReIcon';
import ReSelect from '../components/ReSelect';
import Autogrow from '../components/Autogrow';
import ReSlider from '../components/ReSlider';
import ReStepper from '../components/ReStepper';

window.Alpine = Alpine;
export default () => {
    Alpine.directive('tooltip', Tooltip);
    Alpine.directive('autogrow', Autogrow);
    Alpine.directive('numberonly', NumberOnly);

    Alpine.data('xReStepper', ReStepper.xReStepper);
    Alpine.data('xValidateField', Validator.xValidate);
    Alpine.data('xReIcon', ReIcon.xReIcon);
    Alpine.data('xReSelect', ReSelect.xReSelect);
    Alpine.data('xReSlider', ReSlider.xReSlider);
};
