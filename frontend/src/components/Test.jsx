import { useEffect, useState } from 'preact/hooks';

import TestSection from './TestSection';
import AnswerCard from './AnswerCard';

//TODO zatím zde provizorně, potom by se z backendu měla fetchovat reálná data v tomto formátu
const cards = [
    {
        id: 1,
        name: 'Název přírodniny',
        src: 'images/logo.png',
    },
    {
        id: 2,
        name: 'Název přírodniny',
        src: 'images/logo.png',
    },
    {
        id: 3,
        name: 'Název přírodniny',
        src: 'images/logo.png',
    },
    {
        id: 4,
        name: 'Název přírodniny',
        src: 'images/logo.png',
    },
    {
        id: 5,
        name: 'Název přírodniny',
        src: 'images/logo.png',
    },
];

const PICTURE_PRESETS = [10, 20, 30];
const TIME_PRESETS = [
    { label: "Žádný limit", value: null },
    { label: "5 minut", value: 300 },
    { label: "10 minut", value: 600 },
];

export default function Test() {

    const [phase, setPhase] = useState("settings"); // "settings" | "test" | "results" | "answers"

    const [numPics, setNumPics] = useState(PICTURE_PRESETS[0]);
    const [customNumPics, setCustomNumPics] = useState("");

    const [presetMinutes, setPresetMinutes] = useState(TIME_PRESETS[0].value);      // v sekundách; null = neomezený čas
    const [timeLimit, setTimeLimit] = useState("");

    const [results, setResults] = useState([]);


    function pickPictures(num)
    {
        setNumPics(num);
        setCustomNumPics("");
    }

    function onCustomNumPics(e)
    {
        const val = e.currentTarget.value;
        setCustomNumPics(val);
        setNumPics(val.trim() === "" ? PICTURE_PRESETS[0] : Number(val)); 
    }

    function pickTime(seconds)
    {
        setPresetMinutes(seconds);
        setTimeLimit("");
    }

    function ontimeLimit(e)
    {
        const val = e.currentTarget.value;
        setTimeLimit(val);
        setPresetMinutes(val.trim() === "" ? TIME_PRESETS[0].value : Number(val) * 60);
    }

    function startTest() {
        setPhase("test");
    }

    function showResults(testResults) {
        setResults(testResults);
        setPhase("results");
    }


    return (
        <>
            {phase === "settings" && (
                <section id="settings-wrapper" class="study-wrapper">

                    <p>Aplikace ti vygeneruje test a spočítá ti statistiky úspěšnosti. Na konci testu si ještě budeš moci projít špatně zodpovězené přírodniny. </p>

                    <form id="test-settings">
                        <div id="pics-num-settings" class="param-settings">
                            <p>Zvol si, kolik otázek bude test obsahovat:</p>
                            <div class="buttons-wrapper flex-row">
                                {PICTURE_PRESETS.map(n => (
                                    <button
                                        type="button"
                                        key={n}
                                        class={`btn border-btn transparent primary test-pics-num-button
                                            ${customNumPics === "" && numPics === n ? " selected" : ""}`}
                                        onClick={() => pickPictures(n)}
                                    >
                                        {n} otázek
                                    </button>
                                ))}
                            </div>
                            <label for="test-pics-num">Jiný počet otázek:</label>
                            <input type="number" id="test-pics-num" min="1"
                                class={`text-field ${customNumPics != "" ? "selected" : ""}`}
                                value={customNumPics} onInput={onCustomNumPics} />
                        </div>

                        <div id="time-limit-settings" class="param-settings">
                            <p>Zvol si časový limit testu:</p>
                            <div class="buttons-wrapper flex-row">
                                {TIME_PRESETS.map(t => (
                                    <button
                                        type="button"
                                        key={t.label}
                                        class={`btn border-btn transparent primary test-time-limit-button
                                            ${timeLimit === "" && presetMinutes === t.value ? " selected" : ""}`}
                                        onClick={() => pickTime(t.value)}
                                    >
                                        {t.label}
                                    </button>
                                ))}
                            </div>
                            <label for="test-time-limit">Jiný čas:</label>
                            <input type="number" id="test-time-limit" min="1"
                                class={`text-field ${timeLimit != "" ? "selected" : ""}`}
                                value={timeLimit} onInput={ontimeLimit} /> minut
                        </div>
                    </form>

                    <button id="start-test-button" class="btn border-btn transparent primary"
                        onClick={startTest}
                    >
                        Spustit test
                    </button>
                </section>
            )}

            {phase === "test" && (
                <TestSection
                    numPics={numPics}
                    timeLimit={timeLimit}
                    onFinish={showResults}
                />
            )}

            {phase === "results" && (
                <section id="results-wrapper" class="study-wrapper">

                    <p>Zodpověděl*a jsi správně 15/20 otázek.</p>
                    <button class="btn border-btn non-transparent primary new-test-button">Otestovat, co mi nešlo</button>

                    <button class="btn border-btn non-transparent primary new-test-button">Zobrazit správné odpovědi</button>
                    <button class="btn border-btn non-transparent primary new-test-button">Nový test</button>

                </section>
            )}

            {phase === "answers" && (
                <section id="answers-wrapper" class="study-wrapper">

                    <div id="cards">
                        {cards.map(card => (
                            <AnswerCard
                                key={card.id}
                                {...card}
                            />
                        ))}
                    </div>

                    <button class="btn border-btn non-transparent primary new-test-button">Nový test</button>
                </section>
            )}
        </>
    );
}