import './stimulus_bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import 'bootstrap';

const MAX_FILE_SIZE = 8 * 1024 * 1024; // 8 MB, matches server-side limit

/* ── Theme toggle ───────────────────────────────────────────── */
function initTheme() {
    document.querySelectorAll('.theme-toggle [data-set-theme]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const theme = btn.dataset.setTheme;
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        });
    });

    window.addEventListener('storage', (e) => {
        if (e.key === 'theme' && e.newValue) {
            document.documentElement.setAttribute('data-theme', e.newValue);
        }
    });
}

/* ── Landing page: GPX upload form ──────────────────────────── */
function initUploadForm() {
    const form = document.querySelector('form[data-roadbook-form]');
    if (!form) {
        return;
    }

    const dropZone = document.getElementById('drop_zone');
    const errorBox = document.getElementById('error');
    const pocketList = document.getElementById('pocket_list');
    const createBtn = document.getElementById('create');

    let gpxContent = null;

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    };

    const clearError = () => {
        errorBox.textContent = '';
        errorBox.classList.add('d-none');
    };

    // The symfony/ux-dropzone Stimulus controller owns the drag & drop UI
    // (placeholder/preview switching, clear button). This code only adds the
    // GPX validation on top of its events.
    const resetDropZone = () => {
        dropZone.querySelector('.dropzone-preview-button').click();
    };

    dropZone.addEventListener('dropzone:clear', () => {
        gpxContent = null;
        dropZone.classList.remove('file-selected');
    });

    dropZone.addEventListener('dropzone:change', (event) => {
        clearError();
        const file = event.detail;

        if (!file.name.toLowerCase().endsWith('.gpx')) {
            resetDropZone();
            showError(`"${file.name}" is not a GPX file.`);
            return;
        }
        if (file.size > MAX_FILE_SIZE) {
            resetDropZone();
            showError(`"${file.name}" exceeds the 8 MB limit.`);
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            const doc = new DOMParser().parseFromString(reader.result, 'application/xml');
            if (doc.documentElement.tagName !== 'gpx') {
                resetDropZone();
                showError(`"${file.name}" is not a valid GPX file.`);
                return;
            }
            gpxContent = reader.result;
            dropZone.classList.add('file-selected');
            dropZone.querySelector('.dz-size').textContent = `${(file.size / (1024 * 1024)).toFixed(1)} MB`;
            if (pocketList) {
                pocketList.value = '';
            }
        };
        reader.readAsText(file, 'UTF-8');
    });

    // A GPX file and a Pocket Query are mutually exclusive sources
    pocketList?.addEventListener('change', () => {
        if (pocketList.value !== '' && gpxContent !== null) {
            resetDropZone();
        }
    });

    // Reveal sort radios only when sorting is enabled
    const sortCheckbox = document.getElementById('sort');
    const sortOptions = document.getElementById('sort_options');
    sortCheckbox?.addEventListener('change', () => {
        sortOptions.classList.toggle('hidden', !sortCheckbox.checked);
    });

    // Advanced options
    const advancedToggle = document.getElementById('toggle_advanced');
    const advancedSection = document.getElementById('advanced_section');
    advancedToggle?.addEventListener('click', (e) => {
        e.preventDefault();
        const shown = advancedSection.classList.toggle('show');
        advancedToggle.textContent = shown ? 'Hide advanced options' : 'Show advanced options';
    });

    /* ── Submit: POST to /upload, then redirect to the roadbook ── */
    const checked = (name) => !!form.querySelector(`input[name="${name}"]`)?.checked;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError();

        const referenceCode = pocketList ? pocketList.value : '';
        if (!gpxContent && referenceCode === '') {
            showError('A GPX file or a Pocket Query is missing.');
            return;
        }

        const localeSelect = document.getElementById('locale');
        if (localeSelect.value === '') {
            showError('Please choose a roadbook language.');
            localeSelect.focus();
            return;
        }

        const payload = {
            gpx: gpxContent,
            referenceCode,
            locale: document.getElementById('locale').value,
            theme: document.getElementById('theme')?.value ?? '',
            toc: checked('toc'),
            note: checked('note'),
            long_desc: checked('long_desc'),
            hint: checked('hint'),
            waypoints: checked('waypoints'),
            spoilers: checked('spoilers'),
            logs: checked('logs'),
            pagebreak: checked('pagebreak'),
            images: checked('images'),
            sort_by: checked('sort') ? (form.querySelector('input[name="sort_by"]:checked')?.value ?? '') : '',
        };

        const originalLabel = createBtn.textContent;
        createBtn.disabled = true;
        createBtn.textContent = 'Generating…';

        try {
            const response = await fetch('/upload', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (!response.ok) {
                throw new Error(`Server error (${response.status})`);
            }
            const data = await response.json();
            if (!data.success) {
                showError(data.message || 'Roadbook generation failed.');
                return;
            }
            window.location.href = data.redirect;
        } catch (err) {
            showError(`Roadbook generation failed: ${err.message}`);
        } finally {
            createBtn.disabled = false;
            createBtn.textContent = originalLabel;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initUploadForm();
});
