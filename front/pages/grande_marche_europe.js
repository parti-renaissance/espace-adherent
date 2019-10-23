export default () => {
    // Opening Animation
    setTimeout(() => {
        dom('.logo').style.opacity = 1;
    }, 100);

    setTimeout(() => {
        dom('.splash-screen').style.transform = 'translateY(-150%)';
    }, 800);
};
