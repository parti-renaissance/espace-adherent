import Alpine from 'alpinejs';
import Tooltip from '../components/Tooltip';

window.Alpine = Alpine;

export default () => {
    Alpine.directive('tooltip', Tooltip);

    Alpine.start();
};
