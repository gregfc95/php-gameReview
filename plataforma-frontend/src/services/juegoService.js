import api from '../api/api.js';

export const fetchJuegos = async (page = 1) => {
  try {
    const response = await api.get(`/juegos`, {
      params: {
        pagina: page,
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching games:', error);
    return [];
  }
};
