import 'core-js/stable';
import 'regenerator-runtime/runtime';
import 'utils/dom';
import 'utils/css';
import 'utils/text';
import 'utils/url';
import { decode } from 'js-base64';

window.Kernel = class {
    static boot(release, sentryDsn, environment, algoliaAppId, algoliaAppPublicKey, algoliaBlacklist) {
        Kernel.release = release;
        Kernel.sentryDsn = sentryDsn;

        let app = false;
        let vendor = false;

        const runIfReady = () => {
            if (app && vendor) {
                const { sentryDsn, release, listeners } = Kernel;

                if (sentryDsn) {
                    Raven.config(sentryDsn, { release }).install();
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

Kernel.release = null;
Kernel.sentryDsn = null;
Kernel.listeners = [];
