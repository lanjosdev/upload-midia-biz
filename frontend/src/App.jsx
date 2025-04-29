// Hooks / Libs:
import { BrowserRouter } from 'react-router';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

// Contexts Providers:
import { UserProvider } from './contexts/userContext';

// Components:
import AppRoutes from './routes/AppRoutes'; //Config de rotas

// Estilo Global e StyleElements:
import './styles/global.css';
import './styles/elements.css';


export default function App() {

  return (
    <BrowserRouter>  
      <UserProvider>

        <AppRoutes/>
        <ToastContainer autoClose={3000} limit={1} closeOnClick />
        
      </UserProvider>
    </BrowserRouter>
  )
}