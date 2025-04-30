// Libs:
import api from './config/axiosConfig';
import Cookies from 'js-cookie';

// CONSTANTES:
import { APP_CONSTANTS } from './config/constants';


const UploadVideo = async (file, onProgress) => {
    // console.log('CALL FUNCTION API');
    const bodyFormData = new FormData();
    bodyFormData.append("video", file);

    console.log('Enviando arquivo...');
    const response = await api.post('/upload', bodyFormData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
            const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            onProgress(percentCompleted);
        },
    });

    // console.log(response.data);
    return response.data;
};

const UploadService = {

    // Metodos
    UploadVideo
};

export default UploadService;