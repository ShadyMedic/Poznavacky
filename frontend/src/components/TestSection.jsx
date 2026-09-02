import { useEffect, useState } from 'preact/hooks';
import { useRef } from 'preact/hooks';

import TestImg from './TestImg';
import ProgressBar from './ProgressBar';

export default function TestSection() {

    const [picture, setPicture] = useState(null);
    const [answer, setAnswer] = useState('');

    const pictureManager = useRef(new PictureManager()).current;

    useEffect(() => {
        pictureManager.loadPictures(false, 10, () => {
            setPicture(pictureManager.getNextPicture());
        });
    }, []);

    function handleSubmit(event) {
        event.preventDefault();

        // další obrázek
        setPicture(pictureManager.getNextPicture());

        // vyčištění odpovědi
        setAnswer('');
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

                    <input
                        type="text"
                        autocomplete="off"
                        id="answer"
                        class="text-field"
                        value={answer}
                        onInput={e => setAnswer(e.currentTarget.value)}
                    />

                    <input
                        type="hidden"
                        id="answer-hidden"
                    />

                    <button
                        type="submit"
                        class="btn border-btn non-transparent primary"
                    >
                        Odeslat odpověď
                    </button>
                </form>
            </section>

            <ProgressBar />

        </section>
    );
}