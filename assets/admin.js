import setupTipTap from 'admin/tiptap';
import setupLucide from 'admin/lucide';
import setupTooltips from 'admin/tooltip';
import setupVideoPlayer from 'admin/video-player';

const setupAdmin = () => {
    setupTipTap();
    setupLucide();
    setupTooltips();
    setupVideoPlayer();
};

document.addEventListener('DOMContentLoaded', setupAdmin);
