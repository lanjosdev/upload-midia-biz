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

const Logout = async () => {
    console.log('CALL FUNCTION API');

    const response = await api.post('/logout');

    // console.log(response.data);
    return response.data;
};

const GetCurrentUser = async () => {
    console.log('CALL FUNCTION API');

    const response = await api.get('/profile');

    // console.log(response.data);
    return response.data;
};

const AuthService = {

    // Metodos
    Login,
    Logout,
    GetCurrentUser
};

export default AuthService;