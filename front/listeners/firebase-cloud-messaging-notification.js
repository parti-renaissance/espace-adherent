import * as firebase from 'firebase';

export default (di) => {
    const notifyMeButton = dom('#notify-me');
    const api = di.get('api');

    firebase.initializeApp({
        messagingSenderId: di.get('firebaseMessagingSenderId'),
    });
    const messaging = firebase.messaging();

    if (notifyMeButton) {
        on(notifyMeButton, 'click', () => {
            messaging.requestPermission()
                .then(() => messaging.getToken())
                .then((newToken) => {
                    api.addFirebaseToken(newToken);
                })
                .catch((err) => {
                    // console.log('Unable to get permission to notify.', err);
                })
            ;
        });
    }

    messaging.onTokenRefresh(() => {
        messaging.getToken()
            .then((refreshedToken) => {
                api.addFirebaseToken(refreshedToken);
            })
            .catch((err) => {
                // console.log('Unable to retrieve refreshed token ', err);
            });
    });

    // if the website is on focus, do it inside the page ?
    messaging.onMessage((payload) => {
        // console.log(payload);
    });
};
