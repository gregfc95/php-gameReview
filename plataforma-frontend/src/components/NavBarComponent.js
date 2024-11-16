import React, { useState, useContext } from "react";
import Header from "./HeaderComponent";
import { Link, NavLink, useNavigate } from "react-router-dom";
import { AuthContext } from "../services/AuthProvider";
import "../assets/styles/navBar.css";

function Navbar() {
  const [menuOpen, setMenuOpen] = useState(false);
  const { isAuthenticated, esAdmin, username, logout, loading } =
    useContext(AuthContext);
  const navigate = useNavigate();
  const handleLogout = () => {
    logout();
    navigate("/");
  };
  if (loading) {
    return <div>Loading...</div>;
  }
  return (
    <nav>
      <Header title="Plataforma" />
      <div className="menu" onClick={() => setMenuOpen(!menuOpen)}>
        <span></span>
        <span></span>
        <span></span>
      </div>
      <ul className={menuOpen ? "open" : ""}>
        <li>
          <Link to="/">Home</Link>
        </li>
        <li>
          <NavLink to="/juegos">Juegos</NavLink>
        </li>
        
        {isAuthenticated && esAdmin ? (
          <li>
            <NavLink to="/juego/crear">Crear Juego</NavLink>
          </li>
        ) : (
          "False"
        )}
        {!isAuthenticated ? (
          <>
            <li>
              <NavLink to="/registro">Registro</NavLink>
            </li>
            <li>
              <NavLink to="/login">Login</NavLink>
            </li>
          </>
        ) : null}
        {isAuthenticated ? (
          <>
            <li>
              <span>{username}</span>
            </li>
            <li>
              <button onClick={handleLogout} className="logout-button">
                Logout
              </button>
            </li>
          </>
        ) : null}
      </ul>
    </nav>
  );
}

export default Navbar;
