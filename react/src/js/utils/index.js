// export const animateNb = (value) => {
//     console.log(value);
//     return setInterval((value) => {
//         const target = value;
//         const number = 0;
//         console.log('target', number);
//         if (number >= target) {
//             console.log(value);
//             clearInterval(animateNb);
//         }
//     }, 3300);
// };

// const increment = (value) => {
//     const target = value;
//     const number = number + 1;
//     if (number >= target) {
//         console.log(number);
//     }
// };
//
// setInterval('increment()', 1000);

/*! Visit www.menucool.com for source code, other menu scripts and web UI controls
*  Please keep this notice intact. Thank you. */

export const sse1 = (function () {
    const rebound = 20; // set it to 0 if rebound effect is not prefered
    let slip,
        k;
    return {
        buildMenu() {
            const m = document.getElementById('sses1');
            if (!m) return;
            const ul = m.getElementsByTagName('ul')[0];
            m.style.width = `${ul.offsetWidth + 1}px`;
            const items = m.getElementsByTagName('li');
            const a = m.getElementsByTagName('a');

            slip = document.createElement('li');
            slip.className = 'highlight';
            ul.appendChild(slip);

            const url = document.location.href.toLowerCase();
            k = -1;
            let nLength = -1;
            for (var i = 0; i < a.length; i++) {
                if (-1 != url.indexOf(a[i].href.toLowerCase()) && a[i].href.length > nLength) {
                    k = i;
                    nLength = a[i].href.length;
                }
            }

            if (-1 == k && /:\/\/(?:www\.)?[^.\/]+?\.[^.\/]+\/?$/.test) {
                for (var i = 0; i < a.length; i++) {
                    if ('true' == a[i].getAttribute('maptopuredomain')) {
                        k = i;
                        break;
                    }
                }
                if (-1 == k && 'false' != a[0].getAttribute('maptopuredomain')) k = 0;
            }

            if (-1 < k) {
                slip.style.width = `${items[k].offsetWidth}px`;
                // slip.style.left = items[k].offsetLeft + "px";
                sse1.move(items[k]); // comment out this line and uncomment the line above to disable initial animation
            } else {
                slip.style.visibility = 'hidden';
            }

            for (var i = 0; i < items.length - 1; i++) {
                items[i].onmouseover = function () {
                    if (-1 == k) slip.style.visibility = 'visible';
                    if (this.offsetLeft != slip.offsetLeft) {
                        sse1.move(this);
                    }
                };
            }

            m.onmouseover = function () {
                if (slip.t2) slip.t2 = clearTimeout(slip.t2);
            };

            m.onmouseout = function () {
                if (-1 < k && items[k].offsetLeft != slip.offsetLeft) {
                    slip.t2 = setTimeout(() => {
                        sse1.move(items[k]);
                    }, 50);
                }
                if (-1 == k) {
                    slip.t2 = setTimeout(() => {
                        slip.style.visibility = 'hidden';
                    }, 50);
                }
            };
        },
        move(target) {
            clearInterval(slip.timer);
            const direction = slip.offsetLeft < target.offsetLeft ? 1 : -1;
            slip.timer = setInterval(() => {
                sse1.mv(target, direction);
            }, 15);
        },
        mv(target, direction) {
            if (1 == direction) {
                if (slip.offsetLeft - rebound < target.offsetLeft) this.changePosition(target, 1);
                else {
                    clearInterval(slip.timer);
                    slip.timer = setInterval(() => {
                        sse1.recoil(target, 1);
                    }, 15);
                }
            } else if (slip.offsetLeft + rebound > target.offsetLeft) this.changePosition(target, -1);
            else {
                clearInterval(slip.timer);
                slip.timer = setInterval(() => {
                    sse1.recoil(target, -1);
                }, 15);
            }
            this.changeWidth(target);
        },
        recoil(target, direction) {
            if (-1 == direction) {
                if (slip.offsetLeft > target.offsetLeft) {
                    slip.style.left = `${target.offsetLeft}px`;
                    clearInterval(slip.timer);
                } else slip.style.left = `${slip.offsetLeft + 2}px`;
            } else if (slip.offsetLeft < target.offsetLeft) {
                slip.style.left = `${target.offsetLeft}px`;
                clearInterval(slip.timer);
            } else slip.style.left = `${slip.offsetLeft - 2}px`;
        },
        changePosition(target, direction) {
            if (1 == direction) {
                // following +1 will fix the IE8 bug of x+1=x, we force it to x+2
                slip.style.left = `${slip.offsetLeft +
					Math.ceil(Math.abs(target.offsetLeft - slip.offsetLeft + rebound) / 10) +
					1}px`;
            } else {
                // following -1 will fix the Opera bug of x-1=x, we force it to x-2
                slip.style.left = `${slip.offsetLeft -
					Math.ceil(Math.abs(slip.offsetLeft - target.offsetLeft + rebound) / 10) -
					1}px`;
            }
        },
        changeWidth(target) {
            if (slip.offsetWidth != target.offsetWidth) {
                const diff = slip.offsetWidth - target.offsetWidth;
                if (4 > Math.abs(diff)) slip.style.width = `${target.offsetWidth}px`;
                else slip.style.width = `${slip.offsetWidth - Math.round(diff / 3)}px`;
            }
        },
    };
}());

if (window.addEventListener) {
    window.addEventListener('load', sse1.buildMenu, false);
} else if (window.attachEvent) {
    window.attachEvent('onload', sse1.buildMenu);
} else {
    window.onload = sse1.buildMenu;
}
