// Libs:
import axios from 'axios';
import Cookies from 'js-cookie';

// CONSTANTES:
import { APP_CONSTANTS } from './constants';


// Instância base do Axios para reutilização global
const api = axios.create({
  baseURL: APP_CONSTANTS.API_BASE_URL, // https://midiaapi.rbiz.cc/v1/api
  // timeout: 20000,
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
    // console.log(config);
    return config;
  }, 
  (error) => {
    console.error(error);
    return Promise.reject(error);
  }
);

// Interceptor de resposta para lidar com erros globais
api.interceptors.response.use(
  (response) => response, 
  (error) => {
    if(error?.response?.status === 401) {
      // Exemplo: redirecionar para login se token expirou
      Cookies.remove(APP_CONSTANTS.AUTH_TOKEN_COOKIE_NAME);
      window.location.href = '/';
    }
    // console.error(error);
    return Promise.reject(error);
  }
);


export default api;