import React, { useEffect, useState, useContext,useCallback } from "react";
import { useParams } from "react-router-dom";
import api from "../../api/api.js";
import { AuthContext } from "../../services/AuthProvider";
import '../../assets/styles/detailView.css';

/*
 *JuegoDetailPage: component renders the detail of a single game
 */

function JuegoDetailPage() {
  const { isAuthenticated, user_id } = useContext(AuthContext);
  const { id } = useParams();
  const [juego, setJuego] = useState(null);
  const [calificacion, setCalificacion] = useState(1);
  const [calificacionSubmitted, setCalificacionSubmitted] = useState(false); //bolean para saber si se ha enviado la calificacion
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  const fetchJuegoDetail = useCallback(async () => {
    try {
      const response = await api.get(`/juegos/${id}`);
      console.log(response.data.juego);
      setJuego(response.data.juego);
      if (isAuthenticated) {
        const existingUserRating = response.data.juego.calificaciones.find(
          (cal) => cal.usuario_id === user_id
        );
        if (existingUserRating) {
          setCalificacion(existingUserRating.estrellas); 
          setCalificacionSubmitted(true); 
        }
      }
    } catch (err) {
      setError("Error");
    }
  },[id,isAuthenticated,user_id]);
  useEffect(() => {
    fetchJuegoDetail();
  }, [fetchJuegoDetail]);

  //Estrellas handle
  const handleSubmitRating = async (e) => {
    e.preventDefault();
    if (!isAuthenticated) {
      setError("No estas autenticado.");
      return;
    }

    try {
      let response;
      const estrellasData ={
        juego_id: id,
        estrellas: calificacion,
        usuario_id: user_id,
      };
      if (calificacionSubmitted) {
        //Si existe actualizamos con un put
        console.log(estrellasData);
        response = await api.put(`/calificacion/${id}`, estrellasData);
      } else {
        //Si no existe mandamos con un post
        response = await api.post("/calificacion", {
          juego_id: id,
          estrellas: calificacion,
        });
      }

      if (response.status === 200) {
        setCalificacionSubmitted(true);
        fetchJuegoDetail();
        setSuccessMessage("Calificación enviada con éxito.");
      }
    } catch (err) {
      setError("Error al enviar la calificación.");
    }
  };

  if (error) return <p>{error}</p>;
  return (
    <div className="juego-detail-page">
      {juego ? (
        <>
          <h1>{juego.nombre || "No name"}</h1>
          <p>{juego.descripcion}</p>
          {juego.imagen && <img src={juego.imagen} alt={juego.nombre} />}
          <p>Clasificación Edad: {juego.clasificacion_edad}</p>

          <h3>Plataformas</h3>
          {juego.plataformas && juego.plataformas.length > 0 ? (
            <ul>
              {juego.plataformas.map((plataforma, index) => (
                <li key={index}>{plataforma}</li>
              ))}
            </ul>
          ) : (
            <p>No hay plataformas disponibles.</p>
          )}

          <h3>Estrellas</h3>
          {juego.calificaciones.length > 0 ? (
            juego.calificaciones.map((cal, index) => (
              <div key={index} className={cal.usuario_id === user_id ? "highlight" : ""}>
                <p>Usuario: {cal.nombre_usuario}</p>
                <p>Estrellas: {cal.estrellas}</p>
              </div>
            ))
          ) : (
            <p>No hay calificaciones disponibles.</p>
          )}
          {isAuthenticated ? (
            <form onSubmit={handleSubmitRating}>
              <label htmlFor="rating">Selecciona tu calificación:</label>
              <select
                id="rating"
                value={calificacion}
                onChange={(e) => setCalificacion(Number(e.target.value))}
              >
                {[1, 2, 3, 4, 5].map((star) => (
                  <option key={star} value={star}>
                    {star} Estrella{star > 1 ? "s" : ""}
                  </option>
                ))}
              </select>
              <button type="submit">
              Enviar Calificación
              </button>
            </form>
          ) : null}
          {successMessage && <p>{successMessage}</p>}
        </>
      ) : (
        <p>No Info</p>
      )}
    </div>
  );
}

export default JuegoDetailPage;
