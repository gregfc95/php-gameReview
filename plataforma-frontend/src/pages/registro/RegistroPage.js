import React, { useState } from "react";
import { validateFields, validateUsername, validatePassword } from "../../utils/validationUtils";
import { FaUser, FaLock } from "react-icons/fa6";
import "../../assets/styles/login.css";

const RegistroPage = () => {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState("");

  const handleSubmit = (e) => {
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

    setErrorMessage(''); // Limpiar el mensaje de error
    console.log("Form submitted:", { username, password });
  };


  return (
    <div className="signup-page">
      <form onSubmit={handleSubmit}>
        <h2>Registrate!</h2>
        <div className="input-box">
          <input type="text" 
          placeholder="Usuario" 
          value={username}
          onChange={(e) => setUsername(e.target.value)}
          required />
          <FaUser className="icon" />
        </div>
        <div className="input-box">
          <input type="password" 
          value={password} onChange={(e) => setPassword(e.target.value)} 
          placeholder="Password" 
          required />
          <FaLock className="icon" />
        </div>

        {errorMessage && <p className="error-message">{errorMessage}</p>}

        <button type="submit">Sign up</button>
      </form>
    </div>
  );
};

export default RegistroPage;
