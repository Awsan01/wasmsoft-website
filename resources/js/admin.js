import { initConfirm } from './modules/confirm.js';
import { initCopyButtons } from './modules/copy.js';
import { initDialogs } from './modules/dialog.js';
import { initDismissibles } from './modules/dismiss.js';
import { initDropdowns } from './modules/dropdown.js';
import { initFileInputs } from './modules/file-input.js';
import { initMediaPickers } from './modules/media-picker.js';
import { initShowFor } from './modules/show-for.js';
import { initSubmitState } from './modules/submit-state.js';
import { initTabs } from './modules/tabs.js';

// Order matters: the confirmation handler must see submit events before the
// busy-state handler so unconfirmed submissions are not marked busy.
initConfirm();
initSubmitState();
initDialogs();
initDropdowns();
initTabs();
initDismissibles();
initMediaPickers();
initCopyButtons();
initFileInputs();
initShowFor();
