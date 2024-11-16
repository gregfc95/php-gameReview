import React, { useState, useContext } from "react";
import api from "../../api/api.js";
import {
  validateFields,
  validateUsername,
  validatePassword,
} from "../../utils/validationUtils";
import { useNavigate } from "react-router-dom";
import { AuthContext } from "../../services/AuthProvider.js";
import { FaUser, FaLock } from "react-icons/fa6";
import "../../assets/styles/login.css";

const RegistroPage = () => {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const [passMessage, setPassMessage] = useState("");
  const { login } = useContext(AuthContext); // Login function
  const navigate = useNavigate(); // react v6 hook navigation

  const handleSubmit = async (e) => {
    e.preventDefault(); // Evita que la página se recargue al enviar el formulario

    // Validacion username and password
    const usernameError = validateUsername(username);
    const passwordError = validatePassword(password);
    const fieldsError = validateFields(username, password);

    if (fieldsError) {
      setErrorMessage(fieldsError);
      return;
    }

    if (usernameError) {
      setErrorMessage(usernameError);
      return;
    }

    if (passwordError) {
      setErrorMessage(passwordError);
      return;
    }

    setErrorMessage(""); // Limpiar el mensaje de error
    console.log("Form submitted:", { username, password });

    try {
      const response = await api.post(`/register`, {
        nombre_usuario: username,
        clave: password,
      });

      if (response.status === 200) {
        setPassMessage("Usuario registrado con exito");

        const responseLogin = await api.post(`/login`, {
          nombre_usuario: username,
          clave: password,
        });

        if (responseLogin.status === 200) {
          const { token, vencimiento_token, es_admin, nombre_usuario } =
          responseLogin.data;
          console.log("Login successful:", responseLogin.data);
          login(token, vencimiento_token, es_admin, nombre_usuario);
          //Limpiar los campos
          setUsername("");
          setPassword("");
          //redirigir a la página principal
          navigate("/");
        } else {
          setErrorMessage(
            responseLogin.data.error || "Error al iniciar sesion"
          );
        }
      } else {
        setErrorMessage(response.data.error || "Error al registrar el usuario");
      }
    } catch (error) {
      console.error("Error en el registro:", error);
      setErrorMessage("Error al registrar el usuario");
    }
  };

  return (
    <div className="signup-page">
      <form onSubmit={handleSubmit}>
        <h2>Registrate!</h2>
        <div className="input-box">
          <input
            type="text"
            placeholder="Usuario"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required
          />
          <FaUser className="icon" />
        </div>
        <div className="input-box">
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <FaLock className="icon" />
        </div>

        {errorMessage && <p className="error-message">{errorMessage}</p>}
        {passMessage && <p className="success-message">{passMessage}</p>}
        <button type="submit">Sign up</button>
      </form>
    </div>
  );
};

export default RegistroPage;
