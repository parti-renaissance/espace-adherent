import moment from 'moment';

function initializeTimer(element, refreshPage) {
    const container = find(element, '.clock-container');

    const interval = 1000;
    const diffTime = element.dataset.eventTimestamp - element.dataset.nowTimestamp;
    let duration = moment.duration(diffTime * 1000, 'milliseconds');

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
            s = h = m = d = 0;

            const diff = duration.asMilliseconds() - interval;

            if (0 < diff) {
                duration = moment.duration(diff, 'milliseconds');

                d = moment.duration(duration).days();
                h = moment.duration(duration).hours();
                m = moment.duration(duration).minutes();
                s = moment.duration(duration).seconds();
            } else if (refreshPage) {
                window.location.reload();
            }

            // show how many hours, minutes and seconds are left
            days.innerHTML = ('00' + d).slice(-2);
            hours.innerHTML = ('00' + h).slice(-2);
            minutes.innerHTML = ('00' + m).slice(-2);
            seconds.innerHTML = ('00' + s).slice(-2);
        }, interval);
    }
}

export default (selector, refreshPage = false) => {
    findAll(document, selector).forEach(element => initializeTimer(element, refreshPage));
};
