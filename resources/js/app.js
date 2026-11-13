import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Iniciar Alpine cuando el DOM esté listo, para que los scripts inline
// (Alpine.data(...) dentro de las vistas) ya hayan registrado sus listeners 'alpine:init'.
document.addEventListener('DOMContentLoaded', () => Alpine.start());
