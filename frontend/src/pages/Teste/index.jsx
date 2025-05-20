// Hooks / Libs:
import Cookies from "js-cookie";
import { useEffect, useState } from "react";

// CONTANTES:
import { CONSTANTS_CONFIG } from "../../config/appConfig";

// API:
import UploadService from "../../api/uploadService";

// Contexts:
// import UserContext from "../../contexts/userContext";

// Components:
import { toast } from "react-toastify";
import { HeaderMenu } from "../../components/HeaderMenu/HeaderMenu";
import { LoadingScreen } from "../../components/LoadingScreen/LoadingScreen";

// Assets:
import imgEmpty from '../../assets/images/photo_empty.webp';
// import videoTest from '../../assets/video9_16.mp4';

// Utils:
// import { formatDecimal } from "../../utils/formatNumbers";

// Estilo:
import './style.css';



export default function Teste() {
    // Constantes do componente:
    const configsApp = JSON.parse(Cookies.get(CONSTANTS_CONFIG.COOKIE_CONFIG_NAME) || null);

    const durationLimits = configsApp?.VIDEO.duration_limits || {
        min: 10,
        max: 13
    };
    const megabyteNominal = configsApp?.VIDEO.max_mb_video || 50;
    const megabyteLimit = megabyteNominal * 1024 * 1024;
    const infosNull = {
        name_file: null,
        dimensions: null,
        duration: null,
        size: null
    }; // { name_file: string | null, dimensions: {width: number, height: number}, duration: number, size: number }
    const megabyteChunkNominal = configsApp?.UPLOAD.max_mb_chunk || 3;


    // Estados do componente:
    const [loadingFilePreview, setLoadingFilePreview] = useState(false);
    const [loadingSubmit, setLoadingSubmit] = useState(false);
    const [validationErrors, setValidationErrors] = useState([]);

    // Logica UI:
    const [selectedFile, setSelectedFile] = useState(null);
    const [previewUrl, setPreviewUrl] = useState(null);
    const [infosVideo, setInfosVideo] = useState(infosNull); 

    const [progress, setProgress] = useState(0); 
    const [uploadSuccess, setUploadSuccess] = useState(false); 




    
    useEffect(()=> {
        function initializePage() {
            console.log('Effect /Teste');
        } 
        initializePage();
    }, []);






    return (
        <div className="Page Upload">

            <HeaderMenu />

            <main className='mainPage Upload grid'>
                <div className="main_top">
                    <h1>Upload de Vídeo</h1>
                </div>


                <div className="VideoUploader">
                    {/* Área de pré-visualização */}
                    <div className="container_preview">
                        <div className="preview">
                            {/* Apenas para deixar o elemento na proporção dinamica de 9:16 */}
                            <img src={imgEmpty} alt="" />
                            
                            {loadingFilePreview ? (
                                <div className="preview_empty">
                                    <p>Carregando a pré-visualização...</p>
                                </div>
                            ) : (
                                previewUrl ? (
                                    <video className="preview_video"
                                    src={previewUrl}
                                    controls={true}
                                    muted={true}
                                    // onLoadedMetadata={handleVideoLoad(função que é executada ao carregar video do preview, ai é possivel ver os dados)}
                                    />
                                ) : (
                                    <div className="preview_empty">
                                        <p className="txt_emphasis bold">Selecione um vídeo para upload</p>
                                        <p>
                                            Formato: 1080x1920 <br /> Duração: {durationLimits.min}-{durationLimits.max} segundos <br /> Extensão: .mp4 ou .mov <br /> Tamanho maximo: {megabyteNominal}MB
                                        </p>
                                    </div>
                                )
                            )}
                        </div>
                    </div>

                    {/* Informações do vídeo */}
                    {selectedFile && (
                    <div className="container_infos">
                        <div className="infos">
                            {infosVideo?.name_file && (
                                <p className="filename"><span>Arquivo:</span> {infosVideo.name_file}</p>
                            )}
                            {infosVideo?.dimensions && (
                                <p><span>Dimensões:</span> {infosVideo.dimensions.width}x{infosVideo.dimensions.height}</p>
                            )}
                            {infosVideo?.duration && (
                                <p><span>Duração:</span> {infosVideo.duration} segundos</p>
                            )}
                            {infosVideo?.size && (
                                <p><span>Tamanho:</span> {infosVideo.size} MB</p>
                            )}
                        </div>
                    </div>
                    )}

                    {/* Mensagens de validação */}
                    {validationErrors.length > 0 && (
                    <div className="msg_feedback error">
                        {validationErrors.map((item, idx)=> (
                            <p className="item" key={idx}>
                                <i className="bi bi-exclamation-circle"></i>
                                <span> {item}</span>
                            </p>
                        ))}
                    </div>
                    )}



                    {/* Controle de ações */}
                    <div className="container_btns">
                        <label className="btn cancel" disabled={loadingFilePreview || loadingSubmit}>
                            <input className="none" 
                            type="file"
                            accept="video/mp4" 
                            onChange={handleChangeFile} 
                            disabled={loadingFilePreview || loadingSubmit}
                            />

                            Selecione um vídeo
                        </label>

                        {/* <button className="btn primary"
                        onClick={handleUploadVideo}
                        disabled={!selectedFile || validationErrors.length > 0 || loadingFilePreview || loadingSubmit || uploadSuccess}
                        >
                            {loadingSubmit ? 
                                <span>Enviando...</span>
                            : uploadSuccess ? (
                                <span><i className="bi bi-check-circle-fill"></i> Enviado (Tempo: {timeFileFull}s)</span>
                            ) : (
                                <span>Upload vídeo inteiro</span>
                            )}
                        </button> */}
                        
                        {/* <button className="btn primary"
                        onClick={handleUploadVideoNode}
                        disabled={!selectedFile || validationErrors.length > 0 || loadingFilePreview || loadingSubmit || uploadSuccessNode}
                        >
                            {loadingSubmit ? 
                                <span>Enviando...</span>
                            : uploadSuccessNode ? (
                                <span><i className="bi bi-check-circle-fill"></i> Enviado no Render (Tempo: {timeFileNode}s)</span>
                            ) : (
                                <span>Upload vídeo inteiro (Render)</span>
                            )}
                        </button> */}

                        <button className={`btn ${uploadSuccess ? 'success' : 'primary'}`}
                        onClick={handleUploadVideoChunks}
                        disabled={!selectedFile || validationErrors.length > 0 || loadingFilePreview || loadingSubmit || uploadSuccess}
                        >
                            {loadingSubmit ? 
                                <span>Enviando...</span>
                            : uploadSuccess ? (
                                <span><i className="bi bi-check-circle-fill"></i> Upload feito</span>
                            ) : (
                                <span>Fazer upload</span>
                            )}
                        </button>
                    </div>
                </div>



            </main>

            {loadingSubmit && (
                <LoadingScreen textFeedback={`Realizando upload do vídeo (${progress}%)`} />
            )}
        </div>
    );
}