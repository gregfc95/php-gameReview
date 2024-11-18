import api from '../api/api.js';

export const fetchJuegos = async (page = 1, searchTerm = "", clasificacion = "", plataformas = []) => {
  try {
    const response = await api.get(`/juegos`, {
      params: {
        pagina: page,
        texto: searchTerm,
        clasificacion: clasificacion,
        plataforma: plataformas.length > 0 ? plataformas.join(",") : null,
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching juegos:', error);
    return [];
  }
};
