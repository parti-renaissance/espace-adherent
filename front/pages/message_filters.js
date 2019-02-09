import React from 'react';
import { render } from 'react-dom';
import MessageStatusLoader from '../components/MessageStatusLoader';

export default(api, messageId, synchronized, recipientCount) => {
    render(
        <MessageStatusLoader
            api={api}
            messageId={messageId}
            synchronized={synchronized}
            recipientCount={recipientCount}
        />,
        dom('#message-actions-block')
    );
};
