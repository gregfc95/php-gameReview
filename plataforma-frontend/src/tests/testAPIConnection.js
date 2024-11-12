import axios from 'axios';

export const testAPIConnection = async () => {
  try {
    const response = await axios.get(`${process.env.REACT_APP_API_URL}/juegos?pagina=1`);
    console.log('API Response:', response.data);
  } catch (error) {
    console.error('Error connecting to API:', error.message);
  }
};

