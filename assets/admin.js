import setupTipTap from 'admin/tiptap';
import setupLucide from 'admin/lucide';

const setupAdmin = () => {
    setupTipTap();
    setupLucide();
};

document.addEventListener('DOMContentLoaded', setupAdmin);
