import React from 'react';
import { createRoot } from 'react-dom/client';
import MessageStatusLoader from '../components/MessageStatusLoader';

export default (api, messageId, synchronized, recipientCount, sendLocked) => {
    createRoot(dom('#message-actions-block')).render(
        <MessageStatusLoader
            api={api}
            messageId={messageId}
            synchronized={synchronized}
            recipientCount={recipientCount}
            withResetButton={!!dom('.btn-filter--reset')}
            sendLocked={sendLocked}
        />
    );
};
