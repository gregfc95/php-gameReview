import { Route, Routes } from 'react-router-dom';
import Navbar from './components/NavBarComponent';
import { JuegoPage, JuegoDetailPage, LoginPage, RegistroPage } from './pages';
import FooterComponent from './components/FooterComponent';
//import { useState, useEffect }  from 'react';

//TODO
//Toastify 
function App() {
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
