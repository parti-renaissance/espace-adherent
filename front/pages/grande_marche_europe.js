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
    }, 800);
    setInterval(() => {
        form.style.display = 'flex';
    }, 900);
    setInterval(() => {
        form.style.transform = 'translateY(0)';
    }, 1000);
};
