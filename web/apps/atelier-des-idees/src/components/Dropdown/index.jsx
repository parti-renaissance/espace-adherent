import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import Menu, { Item as MenuItem } from 'rc-menu';
import RCDropdown from 'rc-dropdown';
import 'rc-dropdown/assets/index.css';
import moreIcn from '../../img/icn_20px_more.svg';

// docs: https://github.com/react-component/dropdown
function Dropdown(props) {
    const menu = (
        <Menu onClick={({ key }) => props.onSelect(key)} selectable={false}>
            {props.options.map(option => (
                <MenuItem
                    key={option.value}
                    className={classNames('dropdown-menu__item', {
                        'dropdown-menu__item--important': option.isImportant,
                    })}
                >
                    {option.label}
                </MenuItem>
            ))}
        </Menu>
    );
    return (
        <RCDropdown
            trigger={['click']}
            overlay={menu}
            overlayClassName="dropdown-menu"
            align={{
                points: ['tr', 'br'],
            }}
        >
            <button className="dropdown-button">{props.label || <img src={moreIcn} />}</button>
        </RCDropdown>
    );
}

Dropdown.defaultProps = {
    label: undefined,
};

Dropdown.propTypes = {
    onSelect: PropTypes.func.isRequired,
    label: PropTypes.string,
    options: PropTypes.arrayOf(
        PropTypes.shape({ label: PropTypes.string, value: PropTypes.string, isImportant: PropTypes.bool })
    ),
};

export default Dropdown;
