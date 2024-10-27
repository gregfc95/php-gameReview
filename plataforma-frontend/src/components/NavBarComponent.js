import React, {useState} from "react";
import Header from "./HeaderComponent";
import { Link , NavLink } from "react-router-dom"
import '../assets/styles/navBar.css';

const Navbar = () => {
    const [menuOpen, setMenuOpen] = useState(false);

    return (
        <nav>
            <Header title="Plataforma"/>
            <div className="menu" onClick={() => setMenuOpen(!menuOpen)}>
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul className={menuOpen ? "open" : ""} >
                <li>
                    <Link to="/">Home</Link>
                </li>
                <li>
                    <NavLink to="/juegos">Juegos</NavLink>
                </li>
                <li>
                    <NavLink to="/Registro">Registro</NavLink>
                </li>
                <li>
                    <NavLink to="/Login">Login</NavLink>
                </li>
                <li>
                    <NavLink to="/createGame">Crear Juego</NavLink>
                </li>
            </ul>
        </nav>
    )
}

export default Navbar;