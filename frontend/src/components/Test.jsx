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

export default function Test() {

    return (
        <>
            <section id="settings-wrapper" class="study-wrapper">
                <p>Aplikace ti vygeneruje test a spočítá ti statistiky úspěšnosti. Na konci testu si ještě budeš moci projít špatně zodpovězené přírodniny. </p>
                <form id="test-settings">
                    <p>Zvol si, kolik otázek bude test obsahovat:</p>
                    <div class="buttons-wrapper">
                        <button class="btn border-btn transparent primary test-pics-num-button selected">10 otázek</button>
                        <button class="btn border-btn transparent primary test-pics-num-button">20 otázek</button>
                        <button class="btn border-btn transparent primary test-pics-num-button">30 otázek</button>
                    </div>
                    <label for="test-pics-num">Jiný počet otázek:</label>
                    <input type="text" autocomplete="off" id="test-pics-num" class="text-field" />
                    <p>Zvol si časový limit testu:</p>
                    <div class="buttons-wrapper">
                        <button class="btn border-btn transparent primary test-pics-num-button selected">Žádný limit</button>
                        <button class="btn border-btn transparent primary test-pics-num-button">5 minut</button>
                        <button class="btn border-btn transparent primary test-pics-num-button">10 minut</button>
                    </div>
                    <label for="test-pics-num">Jiný čas:</label>
                    <input type="text" autocomplete="off" id="test-pics-time" class="text-field" />
                </form>
            </section>

            
            <TestSection />

            <section id="results-wrapper" class="study-wrapper">
                <p>Zodpověděl*a jsi správně 15/20 otázek.</p>
                <button class="btn border-btn non-transparent primary new-test-button">Otestovat, co mi nešlo</button>

                <button class="btn border-btn non-transparent primary new-test-button">Zobrazit správné odpovědi</button>
                <button class="btn border-btn non-transparent primary new-test-button">Nový test</button>
            </section>

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
        </>
    );
}