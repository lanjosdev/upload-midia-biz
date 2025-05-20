// Funcionalidades / Libs:
import Cookies from "js-cookie";
import { useState, useContext, useEffect } from 'react';
import { Navigate } from 'react-router-dom';

// CONSTANTES:
import { APP_CONSTANTS } from "../api/config/constants";

// API:
import AuthService from "../api/authService";

// Contexts:
import UserContext from '../contexts/userContext';

// Components:
import { toast } from 'react-toastify';

// Utils:
//import { formatarHora } from '../../../utils/formatarNumbers';

// Assets:
import SpinnerLogo from '../assets/images/BIZSYS_logo_icon.png';



export default function ControllerRouter({ children }) {
    const [loading, setLoading] = useState(true);
    
    const {
        profileDetails, 
        setProfileDetails
    } = useContext(UserContext);



    // Verifica validade do token sempre que acessar rotas privadas SE SIM alimenta profileDetails:
    useEffect(()=> {
        async function checkToken() {
            console.log('Effect ControllerRouter');  

            try {
                // const response = await USER_PROFILE_DETAILS(JSON.parse(tokenCookie));
                const response = await AuthService.GetCurrentUser();
                console.log(response);  
    
                if(response.success) {
                    setProfileDetails(response.data);
                }
                else if(response.success == false) {
                    console.error(response.message);
                }
                else {
                    toast.error('Erro inesperado.');
                    console.warn('Erro inesperado.');
                }
            }
            catch(error) {
                console.error('DETALHES DO ERRO: ', error);

                if(error?.response?.statusText === "Internal Server Error") {

                    // remove cookie-token e profileDetails;
                    Cookies.remove(APP_CONSTANTS.AUTH_TOKEN_COOKIE_NAME)
                    setProfileDetails(null);
                }
            }

            setLoading(false);
        }
        checkToken();
    }, [setProfileDetails]);

    // console.log('Profile: ', profileDetails);
    


    return (
        <>
        {loading ? (

            <div className="loading_route">
                <img src={SpinnerLogo} alt="" />
            </div>

        ) : (
            
            profileDetails ? (
                children
            ) : (
                <Navigate to='/' />
            )
        
        )}
        </>
    )        
}