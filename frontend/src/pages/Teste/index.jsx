// Hooks / Libs:
import { useEffect, useState, useRef } from "react";
import { FFmpeg } from "@ffmpeg/ffmpeg";
import { toBlobURL, fetchFile } from "@ffmpeg/util";

// API:
// import UploadService from "../../api/uploadService";

// Contexts:
// import UserContext from "../../contexts/userContext";

// Components:
// import { toast } from "react-toastify";
import { HeaderMenu } from "../../components/HeaderMenu/HeaderMenu";
import { LoadingScreen } from "../../components/LoadingScreen/LoadingScreen";

// Assets:
// import imgEmpty from '../../assets/images/photo_empty.webp';
// import videoTest from '../../assets/video9_16.mp4';

// Utils:
// import { formatDecimal } from "../../utils/formatNumbers";

// Estilo:
import './style.css';



export default function Teste() {
    const [loaded, setLoaded] = useState(false);
    const [loading, setLoading] = useState(false);
    const ffmpegRef = useRef(new FFmpeg());
    const videoRef = useRef(null);
    const messageRef = useRef(null);

    const [selectedFile, setSelectedFile] = useState(null);
    const [previewUrl, setPreviewUrl] = useState(null);



    
    useEffect(()=> {
        function initializePage() {
            console.log('Effect /Teste');
        } 
        initializePage();
    }, []);



    const load = async () => {
        try {
            setLoading(true);
            const baseURL = "https://unpkg.com/@ffmpeg/core-mt@0.12.6/dist/esm";
            const ffmpeg = ffmpegRef.current;
            ffmpeg.on("log", ({ message }) => {
                if (messageRef.current) messageRef.current.innerHTML = message;
            });
            ffmpeg.on("progress", (ratio) => {
                if (messageRef.current) {
                    messageRef.current.innerHTML = `Progress: ${ratio}`;
                }
                console.log(ratio);
            });
            // toBlobURL is used to bypass CORS issue, urls with the same
            // domain can be used directly.
            await ffmpeg.load({
                coreURL: await toBlobURL(
                    `${baseURL}/ffmpeg-core.js`,
                    "text/javascript"
                ),
                wasmURL: await toBlobURL(
                    `${baseURL}/ffmpeg-core.wasm`,
                    "application/wasm"
                ),
                workerURL: await toBlobURL(
                    `${baseURL}/ffmpeg-core.worker.js`,
                    "text/javascript"
                ),
            });

            setLoading(false);
            setLoaded(true);
        } catch (err) {
            console.log(err);
        }
    };

    const watermark = async () => {
        // const videoURL = "https://imersao-back.onrender.com/1746563705350-20250429_180246.mp4";
        const ffmpeg = ffmpegRef.current;

        await ffmpeg.writeFile("input.mp4", await fetchFile(selectedFile));
        await ffmpeg.writeFile('arial.ttf', await fetchFile('https://raw.githubusercontent.com/ffmpegwasm/testdata/master/arial.ttf'));
        await ffmpeg.exec([
            "-i",
            "input.mp4",
            "-r 30",
            "an",
            "-vf",
            "drawtext=fontfile=/arial.ttf:text='Lucas':x=(w-text_w)/2:y=(h-text_h)/2:fontsize=50:fontcolor=white",
            "output.mp4",
        ]);

        const fileData = await ffmpeg.readFile("output.mp4");
        // const data = new Uint8Array(fileData as ArrayBuffer);
        const data = new Uint8Array(fileData.buffer); // sem "as"

        const fileCompressed = new Blob([data.buffer], { type: "video/mp4" });
        console.log(fileCompressed)
        if(videoRef.current) {
            videoRef.current.src = URL.createObjectURL(fileCompressed);
        }
    };
    

    function resetCurrentData(exceptFile=false) {
        if(previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
        setPreviewUrl(null);
        // setInfosVideo(infosNull);
        if(!exceptFile) {
            setSelectedFile(null);
        }
        // setProgress(0);
    }

    function handleChangeFile(e) {
        const file = e.target.files?.[0] || null;

        // VALIDAÇÕES MINIMAS:
        if(!file) return;


        console.log(file);
        try {
            const fileUrl = URL.createObjectURL(file);
            setSelectedFile(file)
            setPreviewUrl(fileUrl);

        }
        catch(error) {
            console.error(error);
            
            resetCurrentData();
        }
    }



    return (
        <div
        style={{
            margin: "auto",
            padding: "20px",
        }}
        >
            {loaded ? (
                <>
                <video
                style={{
                    height: "500px",
                }}
                ref={videoRef}
                src={previewUrl}
                controls
                >
                </video>

                <br />

                <p ref={messageRef}></p>

                <button className="btn primary" onClick={watermark}>Add Watermark</button>

                <label className="btn cancel">
                    <input className="none" 
                    type="file"
                    accept="video/mp4" 
                    onChange={handleChangeFile} 
                    />

                    Selecione um vídeo
                </label>

                </>
            ) : (
                <>
                {loading && <p>Loading ffmpeg-core...</p>}
                <button className="btn primary" onClick={load}>Load ffmpeg-core</button>
                </>
            )}
        </div>
    );
}