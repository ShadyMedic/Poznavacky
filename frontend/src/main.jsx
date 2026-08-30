import { render } from 'preact';
import ProgressBar from './components/ProgressBar';

import AnswerCard from './components/AnswerCard';

//zatím zde provizorně, potom by se z backendu měly fetchovat reálná data v tomto formátu
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

$(function () {
    $('[data-preact="answer-cards"]').each(function () {
        render(
            <>
                {cards.map(card => (
                    <AnswerCard
                        key={card.id}
                        {...card}
                    />
                ))}
            </>,
            this
        );
    });
});

$(function () {
    $('[data-preact="progress-bar"]').each(function () {
        render(
            <ProgressBar
                value={Number($(this).data('value'))}
                max={Number($(this).data('max'))}
            />,
            this
        );
    });
});