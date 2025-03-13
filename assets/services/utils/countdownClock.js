function initializeTimer(element, refreshPage) {
    const container = findOne(element, '.clock-container');

    const interval = 1000;
    let diffTime = (element.dataset.eventTimestamp - element.dataset.nowTimestamp) * 1000;

    if (0 < diffTime) {
        const days = document.createElement('span');
        days.innerHTML = '00';
        addClass(days, 'days');

        const hours = document.createElement('span');
        hours.innerHTML = '00';
        addClass(hours, 'hours');

        const minutes = document.createElement('span');
        minutes.innerHTML = '00';
        addClass(minutes, 'minutes');

        const seconds = document.createElement('span');
        seconds.innerHTML = '00';
        addClass(seconds, 'seconds');

        container.appendChild(days);
        container.appendChild(hours);
        container.appendChild(minutes);
        container.appendChild(seconds);

        setInterval(() => {
            let d;
            let h;
            let m;
            let s;
            // eslint-disable-next-line no-multi-assign
            s = h = m = d = 0;

            diffTime -= interval;

            if (0 < diffTime) {
                let delta = diffTime / 1000;

                d = Math.floor(delta / 86400);
                delta -= d * 86400;

                h = Math.floor(delta / 3600) % 24;
                delta -= h * 3600;

                m = Math.floor(delta / 60) % 60;
                delta -= m * 60;

                s = delta % 60;
            } else if (refreshPage) {
                window.location.reload();
            }

            // show how many hours, minutes and seconds are left
            days.innerHTML = (`00${d}`).slice(-2);
            hours.innerHTML = (`00${h}`).slice(-2);
            minutes.innerHTML = (`00${m}`).slice(-2);
            seconds.innerHTML = (`00${s}`).slice(-2);
        }, interval);
    }
}

export default (selector, refreshPage = false) => {
    findAll(document, selector).forEach((element) => initializeTimer(element, refreshPage));
};
