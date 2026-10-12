import { useEffect, useState } from 'preact/hooks';
import { useRef } from 'preact/hooks';

import TestImg from './TestImg';
import ProgressBar from './ProgressBar';

export default function TestSection({numPics, timeLimit, onFinish}) {

    const [picture, setPicture] = useState(null); // obrázek (objekt Picture)

    const [answer, setAnswer] = useState(''); // odpověď zadaná uživatelem
    const [checking, setChecking] = useState(false); // odpověď je zrovna kontrolována
    const [error, setError] = useState(null); // chyba kontroly odpovědi

    const [results, setResults] = useState([]);

    const [timeLeft, setTimeLeft] = useState(timeLimit);

    const pictureManager = useRef(new PictureManager()).current;

    // načtení obrázků do testu
    useEffect(() => {
        pictureManager.loadPictures(false, numPics, () => {
            setPicture(pictureManager.getNextPicture());
        });
    }, []);

    // časový odpočet
    useEffect(() => {
        if (timeLimit == null) return; // není časový limit

        // setInterval - prohlížečová funkce volající se opakovaně, parametr 1000 ~ 1 s
        const intervalId = setInterval(() => {
            setTimeLeft(t => (t > 0) ? t - 1 : 0);
        }, 1000)

        return () => clearInterval(intervalId) // vyčištění při odchodu z komponenty
    }, [])

    useEffect(() => {
        if (timeLeft === 0) {
            onFinish(results);
        }
    }, [timeLeft]);

    /**
     * Formátovač času s -> hh:mm:ss
     * @param {int} seconds 
     * @returns string reprezentující čas ve formátu hh:mm:ss
     */
    function formatTime(seconds)
    {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;

        return [h, m, s].map(n => String(n).padStart(2, "0")).join(":");
    }

    async function handleSubmit(e)
    {
        e.preventDefault();    

        setChecking(true);
        setError(null);

        let url = window.location.href;
        if (url.endsWith('/')) { url = url.slice(0, -1); } // odstranění trailing slashe (pokud je přítomen)
        url = url.substring(0, url.lastIndexOf("/")); // odstranění akce (/test)
        
        try {
            const response = await ajaxPost(url + "/check-test-answer", {
                qNum: picture.num,
                ans: answer,
            });

            if (response === null) return;   // redirect in progress

            if (response.messageType === "error") {
                setError(response.message);
                return;
            }

            // TODO zatím první obrázek funguje, je třeba pořešit loadování dalších

            const result = { id: picture.num, userAns: answer, isCorrect: response.message === "correct", correct: response.data.answer };
            const newResults = [...results, result];
            console.log(result);

            if (picture.num + 1 >= numPics)
            {
                // test skončil
                onFinish(newResults);              
            }
            else
            {
                // další obrázek
                setResults(newResults);
                setPicture(pictureManager.getNextPicture());
                setAnswer("");
            }

        } catch (err) {
            setError("Nepodařilo se ověřit odpověď, zkus to znovu.");
        } finally {
            setChecking(false);
        }
    }

    return (
        <section id="test-wrapper" class="study-wrapper">

            <section class="picture">
            
                <TestImg picture={picture} />
                
                <button class="btn report-button icon" title="Nahlásit" >
                    <img class="icon white shadow" src="images/report_f_2.svg" alt="Nahlásit" />
                </button>

                <div class="report-box">
                    {/* report form */}
                </div>
            </section>

            <section class="controllers">
                <form id="answer-form" onSubmit={handleSubmit}>
                    <label for="answer">Zadej odpověď:</label>

                    <input type="text" autocomplete="off" id="answer" class="text-field"
                        value={answer}
                        onInput={e => setAnswer(e.currentTarget.value)}
                    />

                    <button type="submit" class="btn border-btn non-transparent primary">
                        Odeslat odpověď
                    </button>
                </form>
            </section>

            <ProgressBar 
                current={results.length+1}
                max={numPics} />

            {timeLimit != null && (
                <div class="time-counter">Zbývající čas: {formatTime(timeLeft)}</div>
            )}

        </section>
    );
}