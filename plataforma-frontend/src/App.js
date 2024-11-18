import React, { useEffect } from "react";
import { Route, Routes, useLocation } from "react-router-dom";
import Navbar from "./components/NavBarComponent";
import { JuegoPage, JuegoDetailPage, JuegoCreatePage, LoginPage, RegistroPage } from "./pages";
import FooterComponent from "./components/FooterComponent";
//import { testAPIConnection } from "./tests/testAPIConnection";
import { AuthProvider } from './services/AuthProvider';
//TODO
//Toastify
function App() {
  const location = useLocation();
  const appName = "Plataforma";

  useEffect(() => {
    const path =
      location.pathname === "/" ? "Home" : location.pathname.replace("/", "");
      document.title = `${path} | ${appName}`;
    
   // testAPIConnection();
  }, [location]);

  return (
    <>
        <AuthProvider>
      <Navbar />
      <Routes>
        <Route path="/" element={<JuegoPage />} />
        <Route path="/juegos/:id" element={<JuegoDetailPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/registro" element={<RegistroPage />} />
        <Route path="/juego/crear" element={<JuegoCreatePage />} />
      </Routes>
      <FooterComponent name="Jose Gregorio Fernandez Campos" group="6" />
      </AuthProvider>
    </>
  );
}

export default App;
