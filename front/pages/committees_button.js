import Dropdown from 'rc-dropdown';
import React from 'react';
import ReactDOM from 'react-dom';

export default () => {
    const elements = document.getElementsByClassName('feed-rc-dropdown');

    for (const element of elements) {
        const menu = (
          <ul>
            <li><a className={'btn'} href={element.dataset.urlEdit}>Modifier le message</a></li>
            <li><a className={'btn btn--error'} href={element.dataset.urlDelete}>Supprimer le message</a></li>
          </ul>
        );

        ReactDOM.render(
          <div>
            <Dropdown
              trigger={['click']}
              overlay={menu}
              animation="slide-up"
            >
              <span>...</span>
            </Dropdown>
          </div>
          , element);
    }
};
