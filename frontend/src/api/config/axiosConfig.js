// Libs:
import axios from 'axios';
import Cookies from 'js-cookie';
import { toast } from 'react-toastify';

// CONSTANTES:
import { APP_CONSTANTS } from './constants';


// Instância base do Axios para reutilização global
const api = axios.create({
  baseURL: APP_CONSTANTS.API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});


// Interceptor para injetar tokens em requisições
api.interceptors.request.use(
  (config) => {
    const token = Cookies.get(APP_CONSTANTS.AUTH_TOKEN_COOKIE_NAME);
    if(token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    console.warn(config);
    toast.warn(config);
    return config;
  }, 
  (error) => {
    console.error(error);
    toast.error(error);
    return Promise.reject(error);
  }
);

// Interceptor de resposta para lidar com erros globais
api.interceptors.response.use(
  (response) => response, 
  (error) => {
    if(error.response?.status === 401) {
      // Exemplo: redirecionar para login se token expirou
      window.location.href = '/';
    }
    console.error(error);
    toast.error(error);
    return Promise.reject(error);
  }
);


export default api;