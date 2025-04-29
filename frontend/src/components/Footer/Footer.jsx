// Funcionalidades / Libs:
// import { useState, useEffect } from 'react';
// import { Navigate } from 'react-router-dom';

// Utils:
//import { formatarHora } from '../../../utils/formatarNumbers';

// Assets:


// Estilo:
import './footer.css';


export function Footer() {
    const atual = new Date(); //cria uma nova instância do objeto Date 
    const anoAtual = atual.getFullYear();

    return (
        <footer className="Footer">
            <p>&copy;{anoAtual} Bizsys</p>
        </footer>
    )        
}