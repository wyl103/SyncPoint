// public/js/modules/utils.js
// Funciones auxiliares y utilidades generales de fecha, hora y formato.

const formatLocalIso = (d) => {
    if (!(d instanceof Date) || isNaN(d)) d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const getDayName = (d) => {
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return dias[d.getDay()];
};

function formatFechaHeaderChatwoot(createdAt) {
    if (!createdAt) return null;
    const date = (typeof createdAt === 'number' || !isNaN(createdAt)) 
        ? new Date(Number(createdAt) * 1000) 
        : new Date(createdAt);

    if (isNaN(date.getTime())) return null;

    const hoy = new Date();
    const ayer = new Date();
    ayer.setDate(hoy.getDate() - 1);

    if (date.toDateString() === hoy.toDateString()) return 'Hoy';
    if (date.toDateString() === ayer.toDateString()) return 'Ayer';

    return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatHoraChatwoot(createdAt) {
    if (!createdAt) return '';
    const date = (typeof createdAt === 'number' || !isNaN(createdAt)) 
        ? new Date(Number(createdAt) * 1000) 
        : new Date(createdAt);

    if (isNaN(date.getTime())) return '';

    return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });
}

function max(a, b) {
    return a > b ? a : b;
}
