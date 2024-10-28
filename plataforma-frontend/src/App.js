import React, { useEffect } from "react";
import { Route, Routes, useLocation } from "react-router-dom";
import Navbar from "./components/NavBarComponent";
import { JuegoPage, JuegoDetailPage, LoginPage, RegistroPage } from "./pages";
import FooterComponent from "./components/FooterComponent";

//TODO
//Toastify
function App() {
  const location = useLocation();
  const appName = "Plataforma";

  useEffect(() => {
    const path =
      location.pathname === "/" ? "Home" : location.pathname.replace("/", "");
    document.title = `${path} | ${appName}`;
  }, [location]);

  return (
    <>
      <Navbar />
      <Routes>
        <Route path="/" element={<JuegoPage />} />
        <Route path="/juego/:id" element={<JuegoDetailPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/registro" element={<RegistroPage />} />
      </Routes>

      <FooterComponent name="Jose Gregorio Fernandez Campos" group="6" />
    </>
  );
}

export default App;
