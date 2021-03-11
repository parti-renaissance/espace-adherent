import 'core-js/stable';
import 'regenerator-runtime/runtime';
import 'utils/dom';
import 'utils/css';
import 'utils/text';
import 'utils/url';

window.Kernel = class {
    static boot(release, sentryDsn, environment, algoliaAppId, algoliaAppPublicKey, algoliaBlacklist) {
        Kernel.release = release;
        Kernel.sentryDsn = sentryDsn;

        let app = false,
            vendor = false;

        const runIfReady = () => {
            if (app && vendor) {
                const sentryDsn = Kernel.sentryDsn;
                const release = Kernel.release;
                const listeners = Kernel.listeners;

                if (sentryDsn) {
                    Raven.config(sentryDsn, { release }).install();
                }

                for (const i in listeners) {
                    App.addListener(listeners[i]);
                }

                App.run({
                    sentryDsn,
                    release,
                    environment,
                    algoliaAppId,
                    algoliaAppPublicKey,
                    algoliaBlacklist: Base64.decode(algoliaBlacklist).split(','),
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
