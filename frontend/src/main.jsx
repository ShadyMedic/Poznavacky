import { render } from 'preact';

import Test from './components/Test';

$(function () {
    $('[data-preact="test"]').each(function () {
        render(
            <Test />,
            this
        );
    });
});


