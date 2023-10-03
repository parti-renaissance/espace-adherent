import 'utils/dom';
import 'utils/sharer';
import 'utils/css';

import * as Sentry from '@sentry/browser';

import './style/main.scss';

window.Bootstrap = class {
    static boot(release, sentryDsn, environment, user) {
        let app = false;

        const runIfReady = () => {
            if (app) {
                const { listeners } = Bootstrap;

                if (sentryDsn) {
                    Sentry.init({
                        dsn: sentryDsn,
                        release,
                        environment,
                        integrations: [new Sentry.BrowserTracing()],
                        ignoreErrors: ['Non-Error promise rejection captured'],
                        tracesSampleRate: 0.025,
                        denyUrls: ['axept.io'],
                    });

                    if (user) {
                        Sentry.setUser({ email: user });
                    }
                }

                listeners.forEach((listener) => Main.addListener(listener));

                Main.run({ sentryDsn, release, environment });
            }
        };

        import('./main').then(() => {
            app = true;
            runIfReady();
        });
    }

    static onLoad(callback) {
        Bootstrap.listeners.push(callback);
    }
};

Bootstrap.listeners = [];
