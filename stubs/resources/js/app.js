import * as Turbo from '@hotwired/turbo';

import './bootstrap';
import './elements/turbo-echo-stream-tag';

//=inject-alpine
//=inject-stimulus

Turbo.start();

window.Turbo = Turbo;
