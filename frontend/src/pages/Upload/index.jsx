// Hooks / Libs:
import { useEffect, useState } from "react";

// API:
import UploadService from "../../api/uploadService";

// Contexts:
// import UserContext from "../../contexts/userContext";

// Components:
import { toast } from "react-toastify";
import { HeaderMenu } from "../../components/HeaderMenu/HeaderMenu";

// Assets:
import imgEmpty from '../../assets/images/photo_empty.webp';
// import videoTest from '../../assets/video9_16.mp4';

// Utils:
import { formatDecimal } from "../../utils/formatNumbers";

// Estilo:
import './style.css';



export default function Upload() {
    // Variaveis padrão:
    const durationLimits = {
        min: 10,
        max: 13
    };
    const megabyteNominal = 50;
    const megabyteLimit = megabyteNominal * 1048576;
    const infosNull = {
        name_file: null,
        dimensions: null,
        duration: null,
        size: null
    }; // { name_file: string | null, dimensions: {width: number, height: number}, duration: number, size: number }

    // Estados do componente:
    const [loadingFilePreview, setLoadingFilePreview] = useState(false);
    const [loadingSubmit, setLoadingSubmit] = useState(false);
    // const [hasError, setHasError] = useState(false);
    const [validationErrors, setValidationErrors] = useState([]);
    // const [validationMessage, setValidationMessage] = useState([]); // [{type: "success" | "error" | null, message: string}]

    // Logica UI:
    const [selectedFile, setSelectedFile] = useState(null);
    const [previewUrl, setPreviewUrl] = useState(null);
    const [infosVideo, setInfosVideo] = useState(infosNull); 
    const [progress, setProgress] = useState(0); 
    const [uploadSuccess, setUploadSuccess] = useState(false); 



    // // Check validations
    // const isValidDuration = videoDuration !== null && videoDuration >= 10 && videoDuration <= 20;
    // const isValidAspectRatio = aspectRatio !== null && Math.abs(aspectRatio - 9 / 16) < 0.1;
    // const isVideoValid = isValidDuration && isValidAspectRatio;


    useEffect(()=> {
        function initializePage() {
            console.log('Effect /Upload');
        } 
        initializePage();
    }, []);





    
    function resetCurrentData() {
        if(previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
        setPreviewUrl(null);
        setInfosVideo(infosNull);
        setSelectedFile(null);
    }

    function handleChangeFile(e) {
        const file = e.target.files?.[0] || null;

        // VALIDAÇÕES MINIMAS:
        if(!file) return;

        console.log(file);
        if(file.size > megabyteLimit) {
            setValidationErrors([`O arquivo de vídeo deve ter o tamanho máximo de ${megabyteNominal}MB.`]);
            resetCurrentData();
            return;
        }
        // Verificar se é um arquivo de vídeo mp4
        // if(file.type != 'video/mp4') {
        //     setValidationErrors(['Por favor, selecione um arquivo de vídeo mp4.']);
        //     resetCurrentData();
        //     return;
        // }
        if(!file.type.startsWith("video/")) {
            setValidationErrors(['Por favor, selecione um arquivo de vídeo.']);
            resetCurrentData();
            return;
        }



        // GERA A URL PARA PRÉ-VISUALIZAÇÂO E SALVA VIDEO E SUAS INFOS:
        setValidationErrors([]);
        setLoadingFilePreview(true);
        setUploadSuccess(false);

        try {
            const fileUrl = URL.createObjectURL(file);
            setPreviewUrl(fileUrl);
        
            // Coleta infos do vídeo e validações adicionais:
            const errors = [];

            // Verificar se é um arquivo de vídeo mp4
            if(!(file.type == 'video/mp4' || file.name.toLowerCase().endsWith('.mov'))) {
                errors.push(`O arquivo de vídeo deve ser .mp4 ou .mov`);
            }

            const video = document.createElement("video");
            video.preload = "metadata";
            video.onloadedmetadata = () => {
                // Coleta infos do video:
                const name_file = file.name;
                const dimensions = {
                    width: video.videoWidth,
                    height: video.videoHeight
                };
                const duration = video.duration.toFixed(1) || video.duration;
                const size = formatDecimal(file.size / 1048576);
                // console.log(size);
                setInfosVideo({ 
                    ...infosNull,
                    name_file,
                    dimensions,
                    duration,
                    size
                });

                
                // Validações adicionais:
                // Verificar proporção (1080x1920)
                if(dimensions.width !== 1080 || dimensions.height !== 1920) {
                    errors.push(`O vídeo deve ter o formato de 1080x1920`);
                }

                // Verificar duração (10-15 segundos)
                if (duration < durationLimits.min || duration > durationLimits.max) {
                    errors.push(`O vídeo deve ter de ${durationLimits.min} a ${durationLimits.max} segundos`);
                } 
                
                setValidationErrors(errors);
                setSelectedFile(file);
                setLoadingFilePreview(false);
            }
            video.src = fileUrl;
        }
        catch(error) {
            console.error(error);
            setValidationErrors(['Houve um erro inesperado.']);
            
            resetCurrentData();
            setLoadingFilePreview(false);
        }
    }


    // SUBMIT API:
    async function handleUploadVideo() {
        setLoadingSubmit(true);

        // VALIDAÇÕES:
        console.log(selectedFile)
        if(!selectedFile) {
            toast.warn('Não há arquivo para fazer upload')
            return;
        }
        
        // Request:
        try {
            const response = await UploadService.UploadVideo(selectedFile, (progress) => {
                // console.log(`Progresso: ${progress}%`);
                // Aqui você pode atualizar uma barra de progresso no estado do React, por exemplo.
                setProgress(progress);
            });
            console.log(response);  

            if(response.success) {
                toast.success('Vídeo enviado com sucesso.');
                setUploadSuccess(true);
            }
            else if(response.success == false) {
                console.warn(response.message);
                toast.warn(response.message);
            }
            else {
                toast.error('Erro inesperado.');
            }
        }
        catch(error) {
            console.error('DETALHES DO ERRO: ', error);
            toast.error('Houve algum erro.');

            setValidationErrors(['Falha no upload.']);
            resetCurrentData();
        }         


        console.log('FIIIIIM')
        setLoadingSubmit(false);
    }
  

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
                                <p><span>Arquivo:</span> {infosVideo.name_file}</p>
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
                        <label className="btn cancel" disabled={loadingSubmit}>
                            <input className="none" 
                            type="file"
                            accept="video/mp4" 
                            onChange={handleChangeFile} 
                            disabled={loadingSubmit}
                            />

                            Selecione um vídeo
                        </label>

                        <button className="btn primary"
                        onClick={handleUploadVideo}
                        disabled={!selectedFile || validationErrors.length > 0 || loadingFilePreview || loadingSubmit || uploadSuccess}
                        >
                            {loadingSubmit ? `Upload (${progress}%)...` : uploadSuccess ? (
                                <span><i class="bi bi-check-circle-fill"></i> Enviado</span>
                            ) : (
                                <span>Fazer upload</span>
                            )}
                        </button>
                    </div>
                </div>



            </main>

        </div>
    );
}