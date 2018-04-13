import React, {Component} from 'react';

const Select = (props) => {
    return (
        <select id="{props.id}" name="{props.name}" className="select__cpt">
            <option value="" selected="selected">Choisir un comité</option>
            <option value="Mr">comité #1</option>
            <option value="Miss">comité #2</option>
            <option value="Mrs">comité #3</option>
            <option value="Ms">comité #4</option>
            <option value="Dr">comité #5</option>
            <option value="Other">comité #6</option>
        </select>
    )
}

export default Select;
