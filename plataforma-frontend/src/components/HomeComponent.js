import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { fetchJuegos } from "../services/juegoService";
import "../assets/styles/gameList.css";

//UseEffect para traer los juegos, fetch data
//UseState para guardar los juegos
function HomeComponent({ searchTerm, clasificacion, plataformas }) {
  //first value of this array will be our current state, e.g. "juegos"
  //second value will be a function that we can use to update our state, e.g. "setJuegos"
  const [juegos, setJuegos] = useState([]);
  const [page, setPage] = useState(1);
  //default state is an empty array

  useEffect(() => {
    const loadjuegos = async () => {
      const data = await fetchJuegos(page, searchTerm, clasificacion, plataformas);
      setJuegos(data);
    };
    loadjuegos();
  }, [page, searchTerm, clasificacion, plataformas]);

  return (
    <>
      <div className="juegos-list">
        {juegos.length > 0 ? (
          juegos.map((juegos) => (
            <div key={juegos.id} className="game-card">
              <Link to={`/juegos/${juegos.id}`} className="game-card-link">
                <h3>{juegos.nombre}</h3>
                <p>{juegos.descripcion}</p>
                {juegos.imagen && (
                  <img src={juegos.imagen} alt={juegos.nombre} />
                )}
                <p>Clasificacion: {juegos.clasificacion_edad}</p>
                {juegos.promedio_calificacion ? (
                  <p>Estrellas: {juegos.promedio_calificacion}</p>
                ) : (
                  <p>Estrellas: None</p>
                )}
                <p>
                  Plataformas:{" "}
                  {Array.isArray(juegos.plataformas) &&
                  juegos.plataformas.length > 0
                    ? juegos.plataformas.join(", ")
                    : "None"}
                </p>
              </Link>
            </div>
          ))
        ) : (
          <p className="no-juegos">No se encontraron juegos.</p>
        )}
      </div>
      <div className="pagination">
        <button onClick={() => setPage((prev) => (prev > 1 ? prev - 1 : 1))}>
          Back
        </button>
        <button onClick={() => setPage((prev) => prev + 1)}>Next</button>
      </div>
    </>
  );
}

export default HomeComponent;
