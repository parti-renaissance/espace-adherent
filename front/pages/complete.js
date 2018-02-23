export default () => {
    const animPart = dom('.complete__anim');
    const completeAnim = dom('.anim');
    const descPart = dom('.complete__desc');
    const completeText = dom('.desc__texts');

    if (1000 < document.body.offsetWidth) {
        setInterval(() => {
            descPart.style.width = '100%';
            animPart.style.width = '35%';
        }, 200);
        setInterval(() => {
            completeText.style.opacity = 1;
            completeText.style.transform = 'translateX(0%)';
            completeText.style.transform = 'translateY(-50%)';
        }, 900);
    }
    window.addEventListener('resize', () => {
        if (1000 > document.body.offsetWidth) {
            window.location.reload();
        }
    });
};
