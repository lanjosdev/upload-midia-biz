// Funcionalidades / Libs:
// import Cookies from "js-cookie";

// Contexts:
// import UserContext from "../../contexts/userContext";

// Components:

// Utils:

// Assets:
// import LogoHeader from '../../assets/logo-header.png';

// Estilo:
import './loaderfeedback.css';


// LoaderFeedback.propTypes = {
//     textFeedback: PropTypes.string,
//     heightStretch: PropTypes.bool
// }
export function LoaderFeedback({ textFeedback, heightStretch }) {
    

    return (
        <div className={`LoaderFeedback ${heightStretch ? 'heightStretch' : ''}`}>
            <span className="loader_content"></span>

            {textFeedback && <p>{textFeedback}</p>}
        </div>
    )        
}