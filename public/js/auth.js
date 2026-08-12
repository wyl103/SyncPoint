document.addEventListener('DOMContentLoaded', () => {
    
    const viewLogin = document.getElementById('view-login');
    const viewApp = document.getElementById('view-app');
    const loadingScreen = document.getElementById('loading-screen');
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    const btnLogout = document.getElementById('btn-logout');
    const btnLogoutMobile = document.getElementById('btn-logout-mobile');
    
    // Detección dinámica de la ruta base según el servidor (Apache vs PHP Built-in Server)
    const currentPath = window.location.pathname;
    const APP_ROOT = currentPath.includes('/app_bless') ? '/app_bless' : '';
    const BASE_PATH = APP_ROOT ? `${APP_ROOT}/public` : '';
    const API_BASE = `${APP_ROOT}/app/api`;

    function mostrarError(mensaje) {
        if (!loginError) return;
        loginError.innerText = mensaje || "Ocurrió un problema al iniciar sesión. Inténtelo más tarde.";
        loginError.classList.remove('hidden-view');
        showLogin();
    }

    async function checkAuthStatus() {
        try {
            const response = await fetch(`${API_BASE}/auth/check_session.php`);
            
            if (!response.ok) {
                showLogin();
                return;
            }

            const data = await response.json();

            if (data && data.authenticated) {
                showApp(data.user);
            } else {
                showLogin();
            }
        } catch (error) {
            console.error("Error validando sesión:", error);
            showLogin();
        } finally {
            if (loadingScreen) {
                loadingScreen.classList.add('hidden-view');
            }
        }
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (loginError) loginError.classList.add('hidden-view');
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch(`${API_BASE}/auth/login.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                
                const rawText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(rawText);
                } catch (jsonErr) {
                    console.error("Respuesta no-JSON del servidor:", rawText);
                    mostrarError("Ocurrió un problema en el servidor. Inténtelo más tarde.");
                    return;
                }

                if (response.ok && data.success) {
                    checkAuthStatus(); 
                } else {
                    mostrarError(data.message || "Credenciales incorrectas");
                }
            } catch (error) {
                console.error("Error de conexión:", error);
                mostrarError("No se pudo conectar con el servidor. Inténtelo más tarde.");
            }
        });
    }

    if (btnLogout) {
        btnLogout.addEventListener('click', realizarLogout);
    }
    if (btnLogoutMobile) {
        btnLogoutMobile.addEventListener('click', realizarLogout);
    }

    async function realizarLogout() {
        try {
            const response = await fetch(`${API_BASE}/auth/logout.php`, { method: 'POST' });
            const result = await response.json();
            if (result.success) {
                document.getElementById('email').value = '';
                document.getElementById('password').value = '';
                showLogin();
            }
        } catch (error) {
            showLogin();
        }
    }

    function showApp(user) {
        if (viewLogin) viewLogin.classList.add('hidden-view');
        if (viewApp) viewApp.classList.remove('hidden-view');

        const userDisplay = document.getElementById('user-name-display');
        if (userDisplay && user) userDisplay.innerText = user.nombre || 'Usuario';
        
        const targetUrl = (BASE_PATH || '') + '/dash';
        if (window.location.pathname !== targetUrl && targetUrl !== '/dash') {
            window.history.pushState({}, '', targetUrl);
        }

        if (typeof initApp === 'function') {
            initApp();
        }
    }

    function showLogin() {
        if (viewApp) viewApp.classList.add('hidden-view');
        if (viewLogin) viewLogin.classList.remove('hidden-view');
        
        const targetUrl = (BASE_PATH || '') + '/login';
        if (window.location.pathname !== targetUrl && targetUrl !== '/login') {
            window.history.pushState({}, '', targetUrl);
        }
    }

    checkAuthStatus();
});