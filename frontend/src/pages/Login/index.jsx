// Hooks / Libs:
import Cookies from "js-cookie";
import { useContext, useState, useRef, useEffect } from "react";
import { useNavigate } from "react-router-dom";

// CONSTANTES:
import { APP_CONSTANTS } from "../../api/config/constants";

// Contexts:
import UserContext from "../../contexts/userContext";

// Components:
import { Footer } from "../../components/Footer/Footer";

// Assets:
import imgLogo from '../../assets/images/LOGO-BIZSYS_preto.png';

// Estilo:
import './style.css';



export default function Login() {
    const navigate = useNavigate();
    const { loading, logarUser } = useContext(UserContext);

    const emailRef = useRef('');
    const passwordRef = useRef('');
    const [showPassword, setShowPassword] = useState(false);





    const tokenCookie = Cookies.get(APP_CONSTANTS.AUTH_TOKEN_COOKIE_NAME);

    useEffect(()=> {
        function verificaTokenCookie() {
            console.log('Effect /Login');
            
            if(tokenCookie) {
                navigate('/upload'); //Será checado a validade do token ao passar para page /upload (onde terá o controller router)
            }
        } 
        verificaTokenCookie();
    }, [navigate, tokenCookie]);

    



    async function handleSubmitLogin(e) {
        e.preventDefault();

        const email = emailRef.current?.value;
        const password = passwordRef.current?.value;

        if(email !== '' && password !== '') {
            logarUser(email, password);
        }        
    } 
  
    return (
        <div className="Page Login">

            <main className='LoginContent animate__animated animate__fadeIn'>
                <div className="logo--welcome">
                    <div className="logo--text">
                        {/* <p className="logo-text">Estoque</p> */}
                        <img className="logo" src={imgLogo} alt="Logotipo" />
                    </div>

                    {/* <h1>Faça seu login no ambiente</h1> */}
                </div>

                <form onSubmit={handleSubmitLogin} autoComplete="off">
                    <div className="input--div">
                        <i className="bi bi-envelope"></i>
                        <input
                            type="email"
                            placeholder='E-mail'
                            ref={emailRef}
                            required
                        />
                    </div>

                    <div className="input--div">
                        <i className="bi bi-key"></i>
                        <input
                            type={showPassword ? 'text' : 'password'}
                            placeholder='Senha'
                            ref={passwordRef}
                            required
                        />
                    </div>

                    <div className="show-password">
                        <input
                        type="checkbox"
                        id='showSenha'
                        onClick={()=> setShowPassword(!showPassword)}
                        />
                        <label htmlFor="showSenha">Mostrar senha</label>
                    </div>

                    <button className="btn primary" disabled={loading}>
                        {loading ? <span className="loader"></span> : 'Entrar'}
                    </button>
                </form>
            </main>

            <Footer/>

        </div>
    );
}