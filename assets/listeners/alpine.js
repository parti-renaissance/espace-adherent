import Alpine from 'alpinejs';
import Tooltip from '../components/Tooltip';
import DepartmentMap from '../components/DepartmentMap';

window.Alpine = Alpine;

export default () => {
    Alpine.directive('tooltip', Tooltip);
    Alpine.data('departmentMap', DepartmentMap)

    Alpine.start();
};
