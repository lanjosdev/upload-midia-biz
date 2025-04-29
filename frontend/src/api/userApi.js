// Config JSON:
import api from '../../public/configApi.json';
// Libs:
import axios from "axios";

// Variaveis:
// Base URL: http://10.10.0.210:8000/api
export const API_URL = api.api_url;
// console.log(API_URL);


// Logar usuario (POST):
export async function USER_LOGIN(email, password) {
   console.log('CALL FUNCTION API');

   const response = await axios.post(`${API_URL}/login`, {
      "email": email,
      "password": password
   },
   { 
      headers: { "Accept": "application/json" } 
   });

   // console.log(response.data);
   return response.data;
}

// Pega detalhes do perfil logado:
export async function USER_PROFILE_DETAILS(token) {
   console.log('CALL FUNCTION API');

   const response = await axios.get(`${API_URL}/my-profile`, {
      headers: { "Accept": "application/json", Authorization: "Bearer " + token } 
   });

   // console.log(response.data);
   return response.data;
}