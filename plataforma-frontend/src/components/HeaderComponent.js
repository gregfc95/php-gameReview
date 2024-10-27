import React from 'react';
import { ReactComponent as Logo } from '../assets/images/logoMenu.svg';
import "../assets/styles/header.css";
//props
const Header = ({ title }) => {
    //destructuring object
    return (
        <header>
            <Logo alt={title} className="logo" />
            <h1 className='title'>{title}</h1>
        </header>
    );

};
export default Header;
