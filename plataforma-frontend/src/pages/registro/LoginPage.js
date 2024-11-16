import React, { useState, useContext } from "react";
import api from "../../api/api.js";
import { AuthContext } from "../../services/AuthProvider.js"
import { useNavigate } from "react-router-dom";
import { validateFields } from "../../utils/validationUtils";
import { FaUser, FaLock } from "react-icons/fa6";
import "../../assets/styles/login.css";

const LoginPage = () => {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const { login } = useContext(AuthContext); // Use the context
  const navigate = useNavigate(); // react v6 hook navigation

  const handleSubmit = async (e) => {
    e.preventDefault(); // Evita que la página se recargue al enviar el formulario

    // Validacion username and password
    const fieldsError = validateFields(username, password);
    if (fieldsError) {
      setErrorMessage(fieldsError);
      return;
    }

    setErrorMessage(""); // Limpiar el mensaje de error
    console.log("Form submitted:", { username, password }); //debugging

    try {
      const response = await api.post(`/login`, {
        nombre_usuario: username,
        clave: password,
      });

      if (response.status === 200) {
        const { token, es_admin, nombre_usuario } = response.data;
        console.log("Login successful:", response.data);
        // Use AuthProvider's login function
        login(token, es_admin, nombre_usuario);

        //Limpiar los campos
        setUsername("");
        setPassword("");
        //Redirigir a la página principal
        navigate("/");
      }
    } catch (error) {
      if (!error.response) {
        setErrorMessage("No hay respuesta del servidor");
      } else if (error.response.status === 400) {
        setErrorMessage(error.response.data.error || "Falta user o password");
      } else if (error.response.status === 401) {
        setErrorMessage(error.response.data.error || "Unauthorized");
      } else {
        console.error("Error connecting to API:", error.message);
        setErrorMessage("Error al iniciar sesión");
      }
    }
  };

  return (
    <div className="login-page">
      <form onSubmit={handleSubmit}>
        <h2>Iniciar Sesion</h2>
        <div className="input-box">
          <input
            type="text"
            placeholder="Nombre de Usuario"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required
          />
          <FaUser className="icon" />
        </div>
        <div className="input-box">
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Contraseña"
            required
          />
          <FaLock className="icon" />
        </div>

        {errorMessage && <p className="error-message">{errorMessage}</p>}

        <button type="submit">Login</button>

        <div className="register-link">
          <p>
            ¿No tienes una cuenta? <a href="/registro">Registrate</a>
          </p>
        </div>
      </form>
    </div>
  );
};

export default LoginPage;
