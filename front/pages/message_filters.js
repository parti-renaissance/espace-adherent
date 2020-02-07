import React from 'react';
import { render } from 'react-dom';
import MessageStatusLoader from '../components/MessageStatusLoader';

export default(api, messageId, synchronized, recipientCount, sendLocked) => {
    render(
        <MessageStatusLoader
            api={api}
            messageId={messageId}
            synchronized={synchronized}
            recipientCount={recipientCount}
            withResetButton={!!dom('.btn-filter--reset')}
            sendLocked={sendLocked}
        />,
        dom('#message-actions-block')
    );
};
