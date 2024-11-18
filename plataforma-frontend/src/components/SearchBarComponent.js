import React from "react";
import "../assets/styles/searchBar.css";

function SearchBar({
  searchTerm,
  onSearchChange,
  clasificacion,
  onClasificacionChange,
  plataformas,
  onPlataformasChange,
}) {
  const handlePlataformaChange = (event) => {
    const { options } = event.target;
    const selectedPlataformas = [];
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        selectedPlataformas.push(options[i].value);
      }
    }
    onPlataformasChange(selectedPlataformas);
  };

  return (
    <div className="search-bar">
      <input
        type="text"
        placeholder="Buscar juegos..."
        value={searchTerm}
        onChange={(e) => onSearchChange(e.target.value)}
      />

      <select
        onChange={(e) => onClasificacionChange(e.target.value)}
        value={clasificacion}
      >
        <option value="">All</option>
        <option value="ATP">ATP</option>
        <option value="+13">+13</option>
        <option value="+18">+18</option>
      </select>

      <select multiple onChange={handlePlataformaChange} value={plataformas}>
        <option value="PS">PS</option>
        <option value="XBOX">XBOX</option>
        <option value="PC">PC</option>
        <option value="Android">Android</option>
        <option value="Otro">Otro</option>
      </select>
    </div>
  );
}

export default SearchBar;
