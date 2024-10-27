import React, { useState } from "react";
import { validateFields} from "../../utils/validationUtils";
import { FaUser, FaLock } from "react-icons/fa6";
import "../../assets/styles/login.css";

const LoginPage = () => {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault(); // Evita que la página se recargue al enviar el formulario
    
    // Validacion username and password
    const fieldsError = validateFields(username, password);

    if (fieldsError) {
      setErrorMessage(fieldsError);
      return;
    }

    setErrorMessage(''); // Limpiar el mensaje de error
    console.log("Form submitted:", { username, password });
  };


  return (
    <div className="login-page">
      <form onSubmit={handleSubmit}>
        <h2>Iniciar Sesion</h2>
        <div className="input-box">
          <input type="text" 
          placeholder="Nombre de Usuario" 
          value={username}
          onChange={(e) => setUsername(e.target.value)}
          required />
          <FaUser className="icon" />
        </div>
        <div className="input-box">
          <input type="password" 
          value={password} onChange={(e) => setPassword(e.target.value)} 
          placeholder="Contraseña" 
          required />
          <FaLock className="icon" />
        </div>

        {errorMessage && <p className="error-message">{errorMessage}</p>}

        <div className="remember-forgot">
          <label>
            <input type="checkbox" /> Recordarme
          </label>
        </div>
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
