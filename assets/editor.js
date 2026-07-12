import 'bootstrap/dist/css/bootstrap.min.css';
import 'jodit/es2021/jodit.min.css';
import './styles/app.css';
import 'bootstrap';
import { Jodit } from 'jodit';

const root = document.getElementById('editor_page');
if (root) {
    initEditorPage(root);
}

function initEditorPage(root) {
    const roadbookId = root.dataset.roadbookId;
    const urls = {
        save: root.dataset.saveUrl,
        remove: root.dataset.deleteUrl,
        export: root.dataset.exportUrl,
        pdf: root.dataset.pdfUrl,
    };
    const savedOptions = JSON.parse(root.dataset.options || '{}');
    const themeCss = savedOptions.theme_css || 'roadbook.css';

    const saveBtn = document.getElementById('btn_save');
    const deleteBtn = document.getElementById('btn_delete');
    const exportForm = document.getElementById('export_form');
    const exportBtn = document.getElementById('btn_export_run');
    const exportStatus = document.getElementById('export_status');
    const pdfLink = document.getElementById('btn_download_pdf');

    let dirty = false;

    const editor = Jodit.make('#editable', {
        height: 900,
        iframe: true,
        iframeCSSLinks: [`/design/${themeCss}`],
        toolbarAdaptive: false,
        showCharsCounter: false,
        showWordsCounter: false,
        buttons: [
            'undo', 'redo', '|',
            'paragraph', 'font', 'fontsize', 'brush', '|',
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'left', 'center', 'right', 'justify', '|',
            'ul', 'ol', 'outdent', 'indent', '|',
            'link', 'image', 'hr', 'table', 'symbols', '|',
            'source', 'fullsize',
        ],
    });

    const setDirty = (value) => {
        dirty = value;
        saveBtn.disabled = !value;
    };

    editor.events.on('change', () => setDirty(true));

    window.addEventListener('beforeunload', (e) => {
        if (dirty) {
            e.preventDefault();
        }
    });

    /* ── Save ───────────────────────────────────────────────── */
    async function save() {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving…';
        try {
            const response = await fetch(urls.save, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: editor.value }),
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'save failed');
            }
            saveBtn.title = data.last_modification;
            setDirty(false);
        } catch (err) {
            alert(`Unable to save the roadbook: ${err.message}`);
            setDirty(true);
        } finally {
            saveBtn.textContent = 'Save';
        }
    }

    saveBtn.addEventListener('click', save);

    /* ── Delete ─────────────────────────────────────────────── */
    deleteBtn.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to delete your roadbook?')) {
            return;
        }
        deleteBtn.disabled = true;
        try {
            const response = await fetch(urls.remove, { method: 'POST' });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'delete failed');
            }
            dirty = false;
            window.location.href = data.redirect;
        } catch (err) {
            alert(`Unable to delete the roadbook: ${err.message}`);
            deleteBtn.disabled = false;
        }
    });

    /* ── Export as PDF ──────────────────────────────────────── */
    for (const [key, value] of Object.entries(savedOptions)) {
        const field = exportForm.elements[key];
        if (!field) {
            continue;
        }
        if (field.type === 'checkbox') {
            field.checked = !!value;
        } else {
            field.value = value;
        }
    }

    const togglePaginationText = (checkbox, input) => {
        input.disabled = checkbox.checked;
        checkbox.addEventListener('change', () => {
            input.disabled = checkbox.checked;
        });
    };
    togglePaginationText(exportForm.elements.header_pagination, exportForm.elements.header_text);
    togglePaginationText(exportForm.elements.footer_pagination, exportForm.elements.footer_text);

    exportBtn.addEventListener('click', async () => {
        if (dirty) {
            await save();
        }

        exportBtn.disabled = true;
        exportStatus.textContent = 'Generating PDF…';
        pdfLink.classList.add('d-none');

        const fields = {};
        for (const el of exportForm.elements) {
            if (!el.name) {
                continue;
            }
            fields[el.name] = el.type === 'checkbox' ? el.checked : el.value;
        }

        try {
            const response = await fetch(urls.export, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(fields),
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'conversion failed');
            }
            exportStatus.textContent = `PDF ready (${data.size} MB)`;
            pdfLink.href = urls.pdf;
            pdfLink.classList.remove('d-none');
        } catch (err) {
            exportStatus.textContent = `Conversion failed: ${err.message}`;
        } finally {
            exportBtn.disabled = false;
        }
    });

    /* ── Theme toggle (shared with the main layout) ─────────── */
    document.querySelectorAll('.theme-toggle [data-set-theme]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const theme = btn.dataset.setTheme;
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        });
    });
}
