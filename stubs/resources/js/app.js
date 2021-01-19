import {
    start as startTurbo
} from '@hotwired/turbo';
import('./bootstrap');
import('./elements/turbo-echo-stream-tag');
//=inject-alpine
//=inject-stimulus

startTurbo();
