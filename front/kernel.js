import 'core-js/stable';
import 'regenerator-runtime/runtime';
import 'utils/dom';
import 'utils/css';
import 'utils/text';
import 'utils/url';
import { decode } from 'js-base64';
import * as Sentry from '@sentry/browser';
import { Integrations } from '@sentry/tracing';

window.Kernel = class {
    static boot(release, sentryDsn, environment, algoliaAppId, algoliaAppPublicKey, algoliaBlacklist, user) {
        let app = false;
        let vendor = false;

        const runIfReady = () => {
            if (app && vendor) {
                const { listeners } = Kernel;

                if (sentryDsn) {
                    Sentry.init({
                        dsn: sentryDsn,
                        release,
                        environment,
                        integrations: [new Integrations.BrowserTracing()],
                        tracesSampleRate: 1.0,
                    });

                    if (user) {
                        Sentry.setUser({ email: user });
                    }
                }

                listeners.forEach((listener) => App.addListener(listener));

                App.run({
                    sentryDsn,
                    release,
                    environment,
                    algoliaAppId,
                    algoliaAppPublicKey,
                    algoliaBlacklist: decode(algoliaBlacklist).split(','),
                });
            }
        };

        const handleError = (error) => {
            throw error;
        };

        System.import('vendor').catch(handleError).then(() => {
            vendor = true;
            runIfReady();
        });

        System.import('app').catch(handleError).then(() => {
            app = true;
            runIfReady();
        });
    }

    static onLoad(callback) {
        Kernel.listeners.push(callback);
    }
};

Kernel.listeners = [];
