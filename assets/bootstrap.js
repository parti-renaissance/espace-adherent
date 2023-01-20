import 'utils/dom';
import 'utils/sharer';
import 'utils/css';

import Alpine from 'alpinejs';
import * as Sentry from '@sentry/browser';
import { Integrations } from '@sentry/tracing';

import departmentMap from './components/DepartmentMap';
import Tooltip from './components/Tooltip';
import './style/main.scss';

window.Alpine = Alpine;
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
                        integrations: [new Integrations.BrowserTracing()],
                        ignoreErrors: ['Non-Error promise rejection captured'],
                        tracesSampleRate: 0.025,
                        denyUrls: ['axept.io'],
                    });

                    if (user) {
                        Sentry.setUser({ email: user });
                    }
                }

                listeners.forEach((listener) => Main.addListener(listener));

                Alpine.data('departmentMap', departmentMap);
                Alpine.directive('tooltip', Tooltip);

                Alpine.start();

                Main.run({
                    sentryDsn,
                    release,
                    environment,
                });
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
