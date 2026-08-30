import { render } from 'preact';
import ProgressBar from './components/ProgressBar';

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