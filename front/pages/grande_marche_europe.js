export default () => {
    const splashScreen = dom('.splash-screen');
    const logo = dom('.logo');
    const form = dom('.gme__title form');

    // Opening Animation
    setInterval(() => {
        logo.style.opacity = 1;
    }, 100);
    setInterval(() => {
        splashScreen.style.transform = 'translateY(-150%)';
    }, 3000);
    setInterval(() => {
        form.style.display = 'flex';
    }, 3100);
    setInterval(() => {
        form.style.transform = 'translateY(0)';
    }, 3200);
};
