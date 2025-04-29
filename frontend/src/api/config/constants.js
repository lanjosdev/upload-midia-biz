export const APP_CONSTANTS = {
    API_BASE_URL: import.meta.env.VITE_API_BASE_URL || 'https://testemidiaapi.bizsys.com.br/api',
    AUTH_TOKEN_COOKIE_NAME: import.meta.env.VITE_APP_AUTH_TOKEN_NAME || 'authTokenMidiaBiz'
};
  
export const ERROR_MESSAGES = {
    NETWORK_ERROR: 'Erro de conexão',
    UNAUTHORIZED: 'Acesso não autorizado',
};