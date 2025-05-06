// Libs:
import api from './config/axiosConfig';
import axios from 'axios';
// import Cookies from 'js-cookie';

// CONSTANTES:
// import { APP_CONSTANTS } from './config/constants';


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

const UploadVideoNode = async (file, onProgress) => {
    // console.log('CALL FUNCTION API');
    const bodyFormData = new FormData();
    bodyFormData.append("video", file);

    console.log('Enviando arquivo no Render...');
    const response = await axios.post('https://imersao-back.onrender.com/upload', bodyFormData, {
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


const UploadVideoChunk = async (chunk, chunkIndex, fileIdName) => {
    // console.log('CALL FUNCTION API');
    const bodyFormData = new FormData();
    bodyFormData.append("chunk", chunk);
    bodyFormData.append("index", chunkIndex);
    bodyFormData.append("filename", fileIdName);

    const response = await api.post('/upload-chunks', bodyFormData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    });

    // console.log(response.data);
    return response.data;
};

const UploadVideoCombineChunks = async (fileIdName, totalChunks) => {
    // console.log('CALL FUNCTION API');
    const bodyReq = {
        filename: fileIdName,
        totalChunks: totalChunks
    };
    console.log(bodyReq);

    const response = await api.post('/union-chunks', bodyReq);

    // console.log(response.data);
    return response.data;
};


const UploadService = {

    // Metodos
    UploadVideo,
    UploadVideoChunk,
    UploadVideoCombineChunks,
    UploadVideoNode
};
export default UploadService;