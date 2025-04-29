// Funcionalidades / Libs:
import Cookies from "js-cookie";
import { useState, useContext, useEffect } from 'react';
import { Navigate } from 'react-router-dom';

// API:
import { USER_PROFILE_DETAILS } from '../API/userApi';

// Contexts:
import UserContext from '../contexts/userContext';

// Components:
import { toast } from 'react-toastify';

// Utils:
//import { formatarHora } from '../../../utils/formatarNumbers';

// Assets:
import SpinnerLogo from '../assets/BIZSYS_logo_icon.png';



export default function ControllerRouter({ children }) {
    const [loading, setLoading] = useState(true);
    
    const {
        profileDetails, 
        setProfileDetails
    } = useContext(UserContext);



    // Verifica validade do token sempre que acessar rotas privadas SE SIM alimenta profileDetails:
    useEffect(()=> {
        async function checkToken()
        {
            console.log('Effect ControllerRouter');
            const tokenCookie = Cookies.get('tokenEstoque') || null;  

            try {
                const response = await USER_PROFILE_DETAILS(JSON.parse(tokenCookie));
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
                if(error?.response?.data?.message == 'Unauthenticated.') {
                    console.error('Requisição não autenticada. Token Invalido!');
                    // remove token e profileDetails;
                    Cookies.remove('tokenEstoque');
                    setProfileDetails(null);
                }
                else {
                    console.error('Houve algum erro.');
                }

                console.error('DETALHES DO ERRO: ', error);
            }

            setLoading(false);
        }
        checkToken();
    }, [setProfileDetails]);

    // console.log('Profile: ', profileDetails);
    


    return (
        <>
        {loading ? (

            <div className="loading-route">
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