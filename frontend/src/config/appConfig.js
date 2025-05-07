// Funcionalidades / Libs:
import axios from "axios";
import Cookies from "js-cookie";
 

// CONSTANTES:
export const CONSTANTS_CONFIG = {
    // BASE_URL: 'http://localhost:5173',
    BASE_URL: import.meta.env.VITE_APP_BASE_URL || 'https://testemidiaapi.bizsys.com.br',
    COOKIE_CONFIG_NAME: import.meta.env.VITE_APP_COOKIE_CONFIG_NAME || 'configAppMidiaBiz',
};
// const BASE_URL = import.meta.env.VITE_APP_BASE_URL || 'https://testemidiaapi.bizsys.com.br';
// export const BASE_URL = 'http://localhost:5173';
// export const COOKIE_CONFIG_NAME = import.meta.env.VITE_APP_COOKIE_CONFIG_NAME || 'configAppMidiaBiz';


// SCRIPTS:
const getConfigApp = async () => {
    console.log('CALL FUNCTION GET CONFIG');

    try {
        const response = await axios.get(`${CONSTANTS_CONFIG.BASE_URL}/configApp.json`);
        console.log(response.data);
        // return response.data;

        const configsApp = response.data;
        Cookies.set(CONSTANTS_CONFIG.COOKIE_CONFIG_NAME, JSON.stringify(configsApp), {
            secure: true,
            sameSite: 'Strict',
            // expires: 1 // 1 dia
        });
    }
    catch (error) {
        console.error('ERRO AO CARREGAR CONFIGURAÇÕES DO APP', error);
        Cookies.remove(CONSTANTS_CONFIG.COOKIE_CONFIG_NAME);
    }
}
getConfigApp();
// const AppConfig = {

//     // Metodos
//     getConfigApp
// };
// export default AppConfig;

// AppConfig.getConfigApp();