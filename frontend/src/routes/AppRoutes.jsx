// Funcionalidades / Libs:
import { Routes, Route } from "react-router-dom";

// Pages:
import Login from "../pages/Login";
import Upload from "../pages/Upload";

// Components:
// import ControllerRouter from "./ControllerRouter";



export default function AppRoutes() {
    return (
        <Routes>

            <Route path="/" element={ <Login /> } />

            <Route path="/upload" element={ <Upload /> } />

        </Routes>
    )
}