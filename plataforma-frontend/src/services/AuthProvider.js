import React, { createContext, useState, useEffect } from "react";
import api from "../api/api.js";

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [esAdmin, setEsAdmin] = useState(false);
  const [username, setUsername] = useState("");
  const [loading, setLoading] = useState(true); // Verificar que haya cargado

  const validateToken = async (token) => {
    try {
      console.log("Validating token...");
      const response = await api.get("/validar-token", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      const expirationDate = new Date(response.data.vencimiento_token);
      const now = new Date();
      if (expirationDate > now) {
        setIsAuthenticated(true);
        setEsAdmin(response.data.es_admin === 1);
        setUsername(response.data.nombre_usuario);
        console.log(response);
      } else {
        setIsAuthenticated(false);
        setEsAdmin(false);
        setUsername("");
        localStorage.clear();
        console.log("session expired");
      }
    } catch (error) {
      setIsAuthenticated(false);
      setEsAdmin(false);
      setUsername("");
      localStorage.clear();
      console.log("error");
    } finally {
      console.log("done");
      setLoading(false);
    }
  };

  useEffect(() => {
    const token = localStorage.getItem("token"); // Check token
    if (token) {
      validateToken(token); // Validate token si logged
    } else {
      localStorage.clear();
      setLoading(false); // No token =  no auth
    }
  }, []);

  const login = (token, es_admin, nombre_usuario) => {
    api.defaults.headers["Authorization"] = `Bearer ${token}`;
    setIsAuthenticated(true);
    setEsAdmin(es_admin);
    setUsername(nombre_usuario);
    localStorage.setItem("token", token);
  };

  const logout = () => {
    localStorage.clear();
    api.defaults.headers["Authorization"] = "";
    setIsAuthenticated(false);
    setEsAdmin(false);
    setUsername("");
  };

  return (
    <AuthContext.Provider
      value={{ isAuthenticated, esAdmin, username, login, logout, loading }}
    >
      {children}
    </AuthContext.Provider>
  );
};
