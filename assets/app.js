import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import { Tooltip } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new Tooltip(el, { trigger: 'hover' });
    });
});
