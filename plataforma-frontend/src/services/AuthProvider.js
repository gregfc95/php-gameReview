import React, { createContext, useState, useEffect, useCallback } from "react";
import api from "../api/api.js";

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [esAdmin, setEsAdmin] = useState(false);
  const [username, setUsername] = useState("");
  const [user_id , setUser_Id] = useState("");
  const [token, setToken] = useState("");
  const [loading, setLoading] = useState(true); // Verificar que haya cargado

  const validateToken = useCallback(async (token) => {
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
        setUsername(response.data.user);
        setUser_Id(response.data.user_id);
        console.log(response.data);
        api.defaults.headers["Authorization"] = `Bearer ${token}`; 
      } else {
        logout();
      }
    } catch (error) {
      logout();
      console.log("error");
    } finally {
      console.log("done");
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    const token = localStorage.getItem("token"); // Check token
    if (token) {
      setToken(token);
      validateToken(token); // Validate token si logged
    } else {
      setLoading(false); // No token =  no auth
    }
  }, [validateToken]);

  const login = (token, es_admin, nombre_usuario) => {
    api.defaults.headers["Authorization"] = `Bearer ${token}`;
    setIsAuthenticated(true);
    setEsAdmin(es_admin);
    setUsername(nombre_usuario);
    setToken(token);
    validateToken(token);
    localStorage.setItem("token", token);
  };

  const logout = () => {
    localStorage.clear();
    delete api.defaults.headers["Authorization"];
    setIsAuthenticated(false);
    setEsAdmin(false);
    setUsername("");
    setUser_Id("");
  };

  return (
    <AuthContext.Provider
      value={{ isAuthenticated, esAdmin, username, user_id, token, login, logout, loading }}
    >
      {children}
    </AuthContext.Provider>
  );
};
