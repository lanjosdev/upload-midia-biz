// Hooks / Libs:
import Cookies from "js-cookie";
import { useContext, useState, useRef } from 'react';
import { Link, NavLink } from "react-router-dom";

// API:

// Contexts:
import UserContext from '../../contexts/userContext';

// Componets:


// Utils:
import { primeiraPalavra } from '../../utils/formatStrings';
//import { formatarHora } from '../../../utils/formatarNumbers';

// Assets:
import imgLogo from '../../assets/images/LOGO-BIZSYS_preto.png';

// Estilo:
import './headermenu.css';



export function HeaderMenu() {
    const {
        loading,
        profileDetails,
        logoutUser
    } = useContext(UserContext);
    
    // const [loadingAlert, setLoadingAlert] = useState(true);
    // const [hasError, setHasError] = useState(true);

    const [isOpen, setIsOpen] = useState(false);
    // const [isOpenAlert, setIsOpenAlert] = useState(false);

    const navMenuRef = useRef(null);
    // const conteinerAlertRef = useRef(null);

    

    // const tokenCookie = Cookies.get('tokenEstoque');



   

    // useEffect(()=> {        
    //     const handleClickOutside = (event) => { 
    //         if(navMenuRef.current && !navMenuRef.current.contains(event.target)) { 
    //             if(isOpen) {
    //                 console.warn('Clique fora do NavMenu!');
    //                 setIsOpen(false);
    //             }
    //         } 

    //         if(conteinerAlertRef.current && !conteinerAlertRef.current.contains(event.target)) { 
    //             if(isOpenAlert) {
    //                 console.warn('Clique fora do conteinerAlert!');
    //                 setIsOpenAlert(false);
    //             }
    //         } 
    //     }; 
        
    //     // Adiciona o listener
    //     document.addEventListener('mousedown', handleClickOutside); 
        
    //     // Cleanup: remove os listeners quando o componente for desmontado
    //     return ()=> { 
    //         document.removeEventListener('mousedown', handleClickOutside); 
    //     };
    // }, [isOpen, isOpenAlert]);

    
    

    return (
        <header className={`HeaderMenu`}>

            <nav ref={navMenuRef} className={`navHeaderMenu grid`}>
                <div className='logo' to='/home' onClick={()=> setIsOpen(false)}>
                    <img src={imgLogo} alt="Logotipo" />
                    {/* <img src={LogoP} className="imgP" alt="Logotipo" /> */}
                </div>

                {/* <ul className={`menu ${isOpen ? 'show' : ''}`}>
                    <li className='mobile'>
                        {loading ? (
                            <p>Carregando...</p>
                        ) : (
                            <NavLink to='/profile' className='btn profile'>
                                {profileDetails?.level === "admin" ? 
                                <i className="bi bi-shield-fill-check"></i> 
                                : 
                                <i className="bi bi-person-circle"></i>
                                }
                                
                                <span className='name-profile'>{primeiraPalavra(profileDetails?.name)}</span>
                            </NavLink>
                        )}
                    </li>
                    
                    <div className="separator mobile"></div>



                    <li>
                        <NavLink to='/home'>Início</NavLink>
                    </li>


                    
                    <div className="separator mobile"></div>

                    <li className='mobile'>
                        <button onClick={logoutUser} disabled={loading}>
                            Sair
                        </button>
                    </li>
                </ul> */}
                
                <div className="menu_right">
                    
                    {loading ? (
                        <small className='desktop'>Carregando...</small>
                    ) : (
                        <div className='btn desktop' to='/profile'>
                            {profileDetails?.level === "admin" ? 
                            <i className="bi bi-shield-fill-check"></i> 
                            : 
                            <i className="bi bi-person-circle"></i>
                            }
                            
                            <span className='name-profile'>
                                {primeiraPalavra(profileDetails?.name || 'usuário')}
                            </span>
                        </div>
                    )}
                    
                    <button className='btn desktop' onClick={logoutUser} disabled={loading}>
                        <span>Sair </span>
                        <i className="bi bi-box-arrow-right"></i>
                    </button>
                    
                    <button className={`btn mobile ${isOpen ? 'open' : ''}`} onClick={()=> setIsOpen(prev => !prev)}>
                    {/* <div className="mobile-menu"> */}
                        <div className="line1"></div>
                        <div className="line2"></div>
                        <div className="line3"></div>
                    {/* </div> */}
                    </button>

                </div>
            </nav>

        </header>
    )        
}