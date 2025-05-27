import setupTipTap from 'admin/tiptap';
import setupLucide from 'admin/lucide';
import setupTooltips from './admin/tooltip';

const setupAdmin = () => {
    setupTipTap();
    setupLucide();
    setupTooltips();
};

document.addEventListener('DOMContentLoaded', setupAdmin);
