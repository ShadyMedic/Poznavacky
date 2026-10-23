import { useEffect, useState } from 'preact/hooks';

import TestSection from './TestSection';
import AnswerCard from './AnswerCard';


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

    const [timeLimit, setTimeLimit] = useState(TIME_PRESETS[0].value); // v sekundách; null = neomezený čas
    const [customMinutes, setCustomMinutes] = useState("");

    const [results, setResults] = useState([]); // výsledky ve formátu [{ id, src, userAns, isCorrect, correct }]

    const correctNum = results.filter(r => r.isCorrect).length; // počet správných odpovědí


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
        setTimeLimit(seconds);
        setCustomMinutes("");
    }

    function oncustomMinutes(e)
    {
        const val = e.currentTarget.value;
        setCustomMinutes(val);
        setTimeLimit(val.trim() === "" ? TIME_PRESETS[0].value : Number(val) * 60);
    }

    function startTest() {
        setPhase("test");
    }

    function showResults(testResults)
    {
        setResults(testResults);
        setPhase("results");
    }

    function showAnswers()
    {
        setPhase("answers");
    }

    function retest()
    {

    }

    function newTest()
    {
        setResults([]);
        setPhase("settings");
        // numPics a timeLimit zůstanou stejné jako v posledním testu
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
                                            ${customMinutes === "" && timeLimit === t.value ? " selected" : ""}`}
                                        onClick={() => pickTime(t.value)}
                                    >
                                        {t.label}
                                    </button>
                                ))}
                            </div>
                            <label for="test-time-limit">Jiný čas:</label>
                            <input type="number" id="test-time-limit" min="1"
                                class={`text-field ${customMinutes != "" ? "selected" : ""}`}
                                value={customMinutes} onInput={oncustomMinutes} /> minut
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

                    <p>Zodpověděl*a jsi správně {correctNum}/{numPics} otázek.</p>
                    <div class="buttons-wrapper flex-row"> 
                        <button class="btn border-btn non-transparent primary new-test-button"
                            onClick={retest}>Otestovat, co mi nešlo</button>
                        <button class="btn border-btn non-transparent primary new-test-button"
                            onClick={newTest}>Nový test</button>
                        <button class="btn border-btn transparent primary new-test-button"
                            onClick={showAnswers}>Zobrazit správné odpovědi</button>
                    </div>

                </section>
            )}

            {phase === "answers" && (
                <section id="answers-wrapper" class="study-wrapper">

                    <div id="cards">
                        {results.map(card => (
                            <AnswerCard
                                key={card.id}
                                name={card.correct}
                                userAns={card.userAns}
                                isCorrect={card.isCorrect}
                                src={card.src}
                            />
                        ))}
                    </div>

                    <div class="buttons-wrapper flex-row"> 
                        <button class="btn border-btn non-transparent primary new-test-button"
                            onClick={retest}>Otestovat, co mi nešlo</button>
                        <button class="btn border-btn non-transparent primary new-test-button"
                            onClick={newTest}>Nový test</button>
                    </div>

                </section>
            )}
        </>
    );
}