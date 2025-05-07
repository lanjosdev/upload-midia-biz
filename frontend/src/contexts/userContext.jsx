// Hooks / Libs:
import Cookies from "js-cookie";
import { createContext, useState } from "react";
import { useNavigate } from "react-router-dom";

// CONSTANTES:
import { APP_CONSTANTS } from "../api/config/constants";

// API:
import AuthService from "../api/authService";

// Cria o Contexto:
const UserContext = createContext();

// Componentes:
import { toast } from "react-toastify";


// Provedor do contexto acima (prove os values(var, states, functions, etc) aos filhos desse provedor):
export function UserProvider({ children }) {
    const navigate = useNavigate();
    
    const [loading, setLoading] = useState(false);
    const [refreshStateContext, setRefreshStateContext] = useState(false);

    const [profileDetails, setProfileDetails] = useState(null);
    const [settingsAdmin, setSettingsAdmin] = useState({
        max_input_quantity: 1000,
        max_input_quantity_min: 100,
    }); //Configs do ambiente para futuras versões (ex: max_input_quantity = 100)
    
    
    // Logar usuario:
    async function logarUser(email, senha) {
        setLoading(true);    
        console.log('Call function Logar do Context...');

        try {
            const response = await AuthService.Login(email, senha);
            console.log(response);
            
            if(response.success) {
                const token = response.data;
                Cookies.set(APP_CONSTANTS.AUTH_TOKEN_COOKIE_NAME, token, { 
                    secure: true,
                    sameSite: 'Strict'
                    // expires: 1 // 1 dia
                });
                toast.success('Login realizado com sucesso!');
                
                navigate('/upload');
            }
            else if(response.success == false) {
                toast.error(response.message);
            }
            else {
                toast.error('Erro inesperado.');
            }
        }
        catch(error) {
            // if(error?.response?.data?.message == 'Unauthenticated.') {
            //     console.error('Requisição não autenticada.');
            // }
            // else {
            //     toast.error('Houve algum erro.');
            // }

            console.error('DETALHES DO ERRO: ', error);
        }
        
        setLoading(false);
    }

    // Logout usuario:
    function removeCookie() {
        setProfileDetails(null);               
        Cookies.remove(APP_CONSTANTS.AUTH_TOKEN_COOKIE_NAME);

        // navigate('/');
        toast.success('Usuário desconectado.');
    }
    async function logoutUser() 
    {
        setLoading(true);    
        console.log('Call function Logout do Context...');

        try {
            // const response = await USER_LOGOUT(JSON.parse(tokenCookie));
            const response = await AuthService.Logout();
            console.log(response);  

            if(response.success || response.message == 'Unauthenticated.') {
                removeCookie();
            }
            else if(response.success == false) {
                toast.error(response.message);
            }
            else {
                toast.error('Erro inesperado.');
            }
        }
        catch(error) {
            // if(error?.response?.data?.message == 'Unauthenticated.') {
            //     console.error('Requisição não autenticada.');
            //     removeCookie();
            // }
            // else {
            //     toast.error('Houve algum erro.');
            // }

            console.error('DETALHES DO ERRO: ', error);
        }

        setLoading(false);
    }

    

    return (
        <UserContext.Provider
        value={{ 
            loading, 
            profileDetails, 
            setProfileDetails, 
            logarUser, 
            logoutUser,
            refreshStateContext, 
            setRefreshStateContext,
            settingsAdmin, 
            setSettingsAdmin
        }} 
        >
            {children}
        </UserContext.Provider>
    )
}

export default UserContext;