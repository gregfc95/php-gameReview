export const validateUsername = (username) => {
    const usernamePattern = /^[a-zA-Z0-9]{6,20}$/; // Alphanumerico, 6-20 characters
    if (!usernamePattern.test(username)) {
      return 'El nombre de usuario debe tener entre 6 y 20 caracteres y ser alfanumerico.';
    }
    
    return null; // No error
  };

  export const validatePassword = (password) => {
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecialChar = /[@$!%*?&]/.test(password);
    const isLongEnough = password.length >= 8;
  
    if (!hasUpperCase || !hasLowerCase || !hasNumber || !hasSpecialChar || !isLongEnough) {
      return 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, números y caracteres especiales.';
    }
    
    return null; // No error
  };

  export const validateFields = (username,password) => {
    if (!username || !password) {
    return "Por favor, ingresa tu nombre de usuario y contraseña.";    
    }
    return null;
  };
