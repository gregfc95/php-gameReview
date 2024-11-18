import React, { useState } from "react";
import HomeComponent from "../../components/HomeComponent";
import SearchBar from "../../components/SearchBarComponent";
import "../../assets/styles/index.css";
/*
 * JuegoPage component renders the list of all games from our DB
 */
function JuegoPage() {
  const [searchTerm, setSearchTerm] = useState("");
  const [clasificacion, setClasificacion] = useState("");
  const [plataformas, setPlataformas] = useState([]);
  return (
    <div>
      <h1>Lista de Juegos</h1>
      <SearchBar
        searchTerm={searchTerm}
        onSearchChange={setSearchTerm}
        clasificacion={clasificacion}
        onClasificacionChange={setClasificacion}
        plataformas={plataformas}
        onPlataformasChange={setPlataformas}
      />
      <HomeComponent
        searchTerm={searchTerm}
        clasificacion={clasificacion}
        plataformas={plataformas}
      />
    </div>
  );
}

export default JuegoPage;
