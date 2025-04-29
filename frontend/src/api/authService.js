// Libs:
import api from './config/axiosConfig';
import Cookies from 'js-cookie';

// CONSTANTES:
import { APP_CONSTANTS } from './config/constants';


const Login = async (email, password) => {
    console.log('CALL FUNCTION API');

    const bodyReq = {
        email: email,
        password: password
    }
    const response = await api.post('/login', bodyReq);

    // console.log(response.data);
    return response.data;
};

const Logout = () => {
    Cookies.remove(APP_CONSTANTS.AUTH_TOKEN_COOKIE_NAME);
    window.location.href = '/';
};

const GetCurrentUser = () => {
  const token = Cookies.get(APP_CONSTANTS.AUTH_TOKEN_COOKIE_NAME);
  return token ? token : null;
};

// Classe para Login e Logout:
const AuthService = {

    // Metodos
    Login
};

export default AuthService;