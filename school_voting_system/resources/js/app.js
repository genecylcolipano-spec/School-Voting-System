import './bootstrap';
import './student-ballot';
import './student-talent-vote';
import './notification-center';

import Alpine from 'alpinejs';
import { responsivePopover } from './responsive-popover';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('responsivePopover', responsivePopover);
});

Alpine.start();
