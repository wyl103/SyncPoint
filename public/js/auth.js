document.addEventListener('DOMContentLoaded', () => {
    
    const viewLogin = document.getElementById('view-login');
    const viewApp = document.getElementById('view-app');
    const viewRegisterFirst = document.getElementById('view-register-first');
    const loadingScreen = document.getElementById('loading-screen');
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    const loginSuccess = document.getElementById('login-success');
    
    const registerFirstForm = document.getElementById('register-first-form');
    const registerFirstError = document.getElementById('register-first-error');

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

    function mostrarErrorRegistro(mensaje) {
        if (!registerFirstError) return;
        registerFirstError.innerText = mensaje || "Ocurrió un problema al registrar el usuario.";
        registerFirstError.classList.remove('hidden-view');
    }

    async function checkAuthStatus() {
        try {
            const response = await fetch(`${API_BASE}/auth/check_session.php`);
            
            if (!response.ok) {
                showLogin();
                return;
            }

            const data = await response.json();

            // Si NO existen usuarios en la base de datos, mostramos el formulario de registro inicial
            if (data && data.has_users === false) {
                showRegisterFirst();
                return;
            }

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

    // Manejo de envío del formulario de REGISTRO del primer usuario
    if (registerFirstForm) {
        registerFirstForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (registerFirstError) registerFirstError.classList.add('hidden-view');
            
            const nombre = document.getElementById('reg-nombre').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;

            try {
                const response = await fetch(`${API_BASE}/auth/register_first_user.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nombre, email, password })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Pre-llenar el correo en el formulario de login y mostrar la pantalla de login
                    const loginEmailInput = document.getElementById('email');
                    if (loginEmailInput) loginEmailInput.value = email;

                    showLogin(data.message);
                } else {
                    mostrarErrorRegistro(data.message || "Error al crear el usuario.");
                }
            } catch (error) {
                console.error("Error al registrar primer usuario:", error);
                mostrarErrorRegistro("No se pudo conectar con el servidor para registrar el usuario.");
            }
        });
    }

    // Manejo de envío del formulario de LOGIN
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (loginError) loginError.classList.add('hidden-view');
            if (loginSuccess) loginSuccess.classList.add('hidden-view');
            
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
        if (viewRegisterFirst) viewRegisterFirst.classList.add('hidden-view');
        if (viewApp) viewApp.classList.remove('hidden-view');

        window.currentUser = user;

        const userDisplay = document.getElementById('user-name-display');
        if (userDisplay && user) userDisplay.innerText = user.nombre || 'Usuario';
        
        // Control de visibilidad del menú Usuarios para Administradores
        const userTipo = (user?.tipo || 'normal').toLowerCase();
        const isAdmin = (userTipo === 'administrador' || userTipo === 'admin');
        
        document.querySelectorAll('.nav-btn[data-target="usuarios"]').forEach(btn => {
            if (isAdmin) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });

        const targetUrl = (BASE_PATH || '') + '/dash';
        if (window.location.pathname !== targetUrl && targetUrl !== '/dash') {
            window.history.pushState({}, '', targetUrl);
        }

        if (typeof initApp === 'function') {
            initApp();
        }
    }

    function showLogin(mensajeExito = null) {
        if (viewApp) viewApp.classList.add('hidden-view');
        if (viewRegisterFirst) viewRegisterFirst.classList.add('hidden-view');
        if (viewLogin) viewLogin.classList.remove('hidden-view');
        
        if (mensajeExito && loginSuccess) {
            loginSuccess.innerText = mensajeExito;
            loginSuccess.classList.remove('hidden-view');
        }

        const targetUrl = (BASE_PATH || '') + '/login';
        if (window.location.pathname !== targetUrl && targetUrl !== '/login') {
            window.history.pushState({}, '', targetUrl);
        }
    }

    function showRegisterFirst() {
        if (viewApp) viewApp.classList.add('hidden-view');
        if (viewLogin) viewLogin.classList.add('hidden-view');
        if (viewRegisterFirst) viewRegisterFirst.classList.remove('hidden-view');

        const targetUrl = (BASE_PATH || '') + '/setup';
        if (window.location.pathname !== targetUrl && targetUrl !== '/setup') {
            window.history.pushState({}, '', targetUrl);
        }
    }

    checkAuthStatus();
});