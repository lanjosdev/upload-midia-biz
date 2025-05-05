// Funcionalidades / Libs:
// import Cookies from "js-cookie";

// Contexts:
// import UserContext from "../../contexts/userContext";

// Components:
import { LoaderFeedback } from "./LoaderFeedback/LoaderFeedback";

// Utils:

// Assets:
// import LogoHeader from '../../assets/logo-header.png';

// Estilo:
import './loadingscreen.css';


// LoadingScreen.propTypes = {
//     textFeedback: PropTypes.string,
//     heightStretch: PropTypes.bool
// }
export function LoadingScreen({ textFeedback, heightStretch }) {    



    return (
        <div className="LoadingScreen animate__animated animate__fadeIn">

            <LoaderFeedback textFeedback={textFeedback} heightStretch={heightStretch} />

        </div>
    )        
}