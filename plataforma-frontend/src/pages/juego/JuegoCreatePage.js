import React, { useState, useContext } from "react";
import api from "../../api/api.js";
import { AuthContext } from "../../services/AuthProvider";
import { validateCreationFields } from "../../utils/validationUtils";
import "../../assets/styles/forms.css";

function JuegoCreatePage() {
  const { token, isAuthenticated  } = useContext(AuthContext);
  const [name, setName] = useState("");
  const [description, setDescription] = useState("");
  const [clasificacion, setClasificacion] = useState("");
  const [plataforma, setPlataforma] = useState([]);
  const [image, setImage] = useState(null);
  const [errorMessage, setErrorMessage] = useState("");
  const [passMessage, setPassMessage] = useState("");
  const imageInputRef = React.useRef(null);

  const handleImageChange = (e) => {
    setImage(e.target.files[0]); //Guardamos en la state variable setImage el archivo
  };

  const handlePlataformaChange = (e) => {
    const selectedOptions = Array.from(e.target.selectedOptions).map(option => option.value);
    setPlataforma(selectedOptions);
    console.log("Plataformas seleccionadas:", selectedOptions);
  };

  const handleSubmit = async (e) => {
    e.preventDefault(); // Evita que la página se recargue al enviar el formulario

    if (!isAuthenticated) {
      setErrorMessage("No está autenticado.");
      return;
    }

    // Validacion de los fields
    const fieldsError = validateCreationFields(
      name,
      description,
      clasificacion,
      plataforma,
      image
    );
    if (fieldsError) {
      setErrorMessage(fieldsError);
      return;
    }

    setErrorMessage(""); // Limpiar el mensaje de error
    console.log("Form submitted:", {
      name,
      description,
      clasificacion,
      plataforma,
      image,
    }); //debugging

    const formData = new FormData();
    formData.append("nombre", name);
    formData.append("descripcion", description);
    formData.append("clasificacion_edad", clasificacion);
    plataforma.forEach((plat, index) =>
      formData.append(`plataforma[${index}]`, plat)
    );
    formData.append("imagen", image);

    try {
      const response = await api.post("/juego", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
          "Authorization": `Bearer ${token}`,
        },
      });

      console.log("Response Status:", response.status);
      console.log("Response Data:", response.data);
      if (response.status === 201) {
        setPassMessage("Juego creado con exito");
        // Limpiar los campos
        setName("");
        setDescription("");
        setClasificacion("");
        setPlataforma([]);
        setImage(null);
        //Reset file upload
        if (imageInputRef.current) {
          imageInputRef.current.value = "";
        }
      } else {
        setErrorMessage(
          response.data.error ||
            "Error al crear el juego. Inténtelo nuevamente."
        );
      }
    } catch (error) {
      console.error("Error al crear el juego:", error);
      setErrorMessage("Error al crear el juego. Inténtelo nuevamente.");
    }
  };

  return (
    <div className="form-main">
      <form onSubmit={handleSubmit}>
        <div className="input-box">
          <label>Nombre:</label>
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Nombre del juego"
            maxLength={45}
          />

          <label>Descripción:</label>
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="Descripción"
          />

          <label>Clasificación:</label>
          <select
            value={clasificacion}
            onChange={(e) => setClasificacion(e.target.value)}
          >
            <option value="">--Seleccione una opción--</option>
            <option value="ATP">ATP</option>
            <option value="+13">+13</option>
            <option value="+18">+18</option>
          </select>

          <label>Imagen:</label>
          <input type="file" accept=".jpeg" onChange={handleImageChange} ref={imageInputRef} />

          <label>Plataforma:</label>
          <select multiple={true}value={plataforma} onChange={handlePlataformaChange}>
            <option value="PS">Playstation</option>
            <option value="XBOX">XBOX</option>
            <option value="PC">PC</option>
            <option value="Android">Android</option>
            <option value="Otro">Otro</option>
          </select>
          {errorMessage && <p className="error-message">{errorMessage}</p>}
          {passMessage && <p className="success-message">{passMessage}</p>}          <button type="submit">Crear</button>
        </div>
      </form>
    </div>
  );
}

export default JuegoCreatePage;
