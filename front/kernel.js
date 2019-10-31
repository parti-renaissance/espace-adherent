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

        let runIfReady = () => {
            if (app && vendor) {
                let sentryDsn = Kernel.sentryDsn;
                let release = Kernel.release;
                let listeners = Kernel.listeners;

                if (sentryDsn) {
                    Raven.config(sentryDsn, { release: release }).install();
                }

                for (let i in listeners) {
                    App.addListener(listeners[i]);
                }

                App.run({
                    sentryDsn: sentryDsn,
                    release: release,
                    environment: environment,
                    algoliaAppId: algoliaAppId,
                    algoliaAppPublicKey: algoliaAppPublicKey,
                    algoliaBlacklist: Base64.decode(algoliaBlacklist).split(','),
                });
            }
        };

        let handleError = (error) => {
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
