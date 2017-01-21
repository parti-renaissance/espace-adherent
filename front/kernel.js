window.Kernel = class {
    static run() {
        let sentryDsn = Kernel.sentryDsn;
        let release = Kernel.release;

        if (sentryDsn) {
            Raven.config(sentryDsn, { release: release }).install();
        }

        App.run({
            sentryDsn: sentryDsn,
            release: release,
        });
    }

    static boot(release, sentryDsn, callback) {
        Kernel.release = release;
        Kernel.sentryDsn = sentryDsn;

        let app = false,
            vendor = false;

        let callCallbackIfReady = () => {
            if (app && vendor) {
                callback();
            }
        };

        let handleError = (error) => {
            throw error;
        };

        System.import('vendor').catch(handleError).then(() => {
            vendor = true;
            callCallbackIfReady();
        });

        System.import('app').catch(handleError).then(() => {
            app = true;
            callCallbackIfReady();
        });
    }
};

Kernel.release = null;
Kernel.sentryDsn = null;
