import Alpine from 'alpinejs';
import departmentMap from '../components/DepartmentMap';
import Tooltip from '../components/Tooltip';

window.Alpine = Alpine;

export default () => {
    Alpine.data('departmentMap', departmentMap);
    Alpine.directive('tooltip', Tooltip);

    Alpine.start();
}
