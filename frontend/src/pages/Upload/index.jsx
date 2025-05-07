// Hooks / Libs:
import { useEffect, useState } from "react";

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
import { formatDecimal } from "../../utils/formatNumbers";

// Estilo:
import './style.css';



export default function Upload() {
    // Constantes do componente:
    const durationLimits = {
        min: 10,
        max: 13
    };
    const megabyteChunkNominal = 3;
    const megabyteNominal = 40;
    const megabyteLimit = megabyteNominal * 1024 * 1024;
    const infosNull = {
        name_file: null,
        dimensions: null,
        duration: null,
        size: null
    }; // { name_file: string | null, dimensions: {width: number, height: number}, duration: number, size: number }

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

        setUploadSuccess(false);
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
        resetCurrentData();

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
    // async function handleUploadVideo() {
    //     setLoadingSubmit(true);
    //     const startTime = performance.now();
    //     setProgress(0)

    //     // VALIDAÇÕES:
    //     // console.log('Original', selectedFile)
    //     if(!selectedFile) {
    //         toast.warn('Não há arquivo para fazer upload')
    //         setLoadingSubmit(false);
    //         resetCurrentData();
    //         return;
    //     }

    //     // Request:
    //     // resetCurrentData(true);
    //     try {
    //         const response = await UploadService.UploadVideo(selectedFile, (progress) => {
    //             setProgress(progress);
    //         });
    //         console.log(response);  

    //         if(response.success) {
    //             toast.success('Vídeo enviado com sucesso.');
    //             setUploadSuccess(true);
    //         }
    //         else if(response.success == false) {
    //             console.warn(response.message);
    //             toast.warn(response.message);
    //         }
    //         else {
    //             toast.error('Erro inesperado.');
    //         }
    //     }
    //     catch(error) {
    //         console.error('DETALHES DO ERRO: ', error);
    //         // toast.error('Houve algum erro.');

    //         setValidationErrors(['Falha no upload.']);
    //         // resetCurrentData();
    //     }         


    //     // console.log('FIIIIIM')
    //     const endTime = performance.now();
    //     const seconds = ((endTime - startTime) / 1000).toFixed(2);
    //     setTimeFileFull(seconds);
    //     setLoadingSubmit(false);
    //     // resetCurrentData();
    // }

    // async function handleUploadVideoNode() {
    //     setLoadingSubmit(true);
    //     const startTime = performance.now();
    //     setProgress(0)

    //     // VALIDAÇÕES:
    //     // console.log('Original', selectedFile)
    //     if(!selectedFile) {
    //         toast.warn('Não há arquivo para fazer upload')
    //         setLoadingSubmit(false);
    //         resetCurrentData();
    //         return;
    //     }

    //     // Request:
    //     // resetCurrentData(true);
    //     try {
    //         const response = await UploadService.UploadVideoNode(selectedFile, (progress) => {
    //             setProgress(progress);
    //         });
    //         console.log(response);  

    //         if(response.url_video) {
    //             toast.success('Vídeo enviado com sucesso.');
    //             setUploadSuccessNode(true);
    //         }
    //         else {
    //             toast.error('Erro inesperado.');
    //         }
    //     }
    //     catch(error) {
    //         console.error('DETALHES DO ERRO: ', error);
    //         // toast.error('Houve algum erro.');

    //         setValidationErrors(['Falha no upload.']);
    //         // resetCurrentData();
    //     }         


    //     // console.log('FIIIIIM')
    //     const endTime = performance.now();
    //     const seconds = ((endTime - startTime) / 1000).toFixed(2);
    //     setTimeFileNode(seconds);
    //     setLoadingSubmit(false);
    //     // resetCurrentData();
    // }

    async function handleUploadVideoChunks() {
        setLoadingSubmit(true);
        // const startTime = performance.now();
        setProgress(0);


        // VALIDAÇÕES:
        // console.log('Arquivo', selectedFile)
        if(!selectedFile) {
            toast.warn('Não há arquivo para fazer upload')
            setLoadingSubmit(false);
            resetCurrentData();
            return;
        }

        // REQUEST:
        try {
            // Tratamento de chunks
            const CHUNK_SIZE = megabyteChunkNominal * 1024 * 1024; // tamanho por chunk
            const totalChunks = Math.ceil(selectedFile.size / CHUNK_SIZE);      
            const fileId = `${Date.now()}-${selectedFile.name}`;
            // console.log(fileId, CHUNK_SIZE, totalChunks)

            for(let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                const start = chunkIndex * CHUNK_SIZE;
                // const end = start + CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, selectedFile.size);
                const chunk = selectedFile.slice(start, end);

        
                // Envie o chunk para API
                // console.log('Envio de chunk index:', chunkIndex)
                const result = await UploadService.UploadVideoChunk(chunk, chunkIndex, fileId);
                // console.log(`Chunck(${chunkIndex+1} de ${totalChunks}) enviado:`, result)
                
                if(result.success) {
                    setProgress(Math.round(((chunkIndex) / totalChunks) * 100));
                }
                // console.warn('Chunk index OK:', chunkIndex)
            }


            // Notifique a API para combinar os chunks após o upload
            const response = await UploadService.UploadVideoCombineChunks(fileId, totalChunks);
            console.log(response);
            
            if(response.success) {
                setProgress(100);
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
            console.error('DETALHES DO ERRO:', error);
            setValidationErrors(['Falha no upload.']); 
        }

        
        // console.log('FIMMMMMMMM')
        // const endTime = performance.now();
        // const seconds = ((endTime - startTime) / 1000).toFixed(2);
        // setTimeChunk(seconds);
        setLoadingSubmit(false);
        // resetCurrentData();
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

                        <button className="btn primary"
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