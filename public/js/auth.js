document.addEventListener('DOMContentLoaded', () => {
    
    // Elementos del DOM... (igual que antes)
    const viewLogin = document.getElementById('view-login');
    const viewApp = document.getElementById('view-app');
    const loadingScreen = document.getElementById('loading-screen');
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    const btnLogout = document.getElementById('btn-logout');
    const btnLogoutMobile = document.getElementById('btn-logout-mobile');
    
    // --- NUEVO: Definir la URL Base ---
    // Esto debe coincidir con lo que pusiste en la etiqueta <base> del HTML
    const BASE_PATH = '/app_bless/public'; 

    async function checkAuthStatus() {
        try {
            // Nota el cambio: Ahora bajamos un nivel desde public/ para llegar a app/api/
            const response = await fetch('../app/api/auth/check_session.php');
            const data = await response.json();

            if (data.authenticated) {
                showApp(data.user);
            } else {
                showLogin();
            }
        } catch (error) {
            console.error("Error validando sesión:", error);
            showLogin();
        } finally {
            loadingScreen.classList.add('hidden-view');
        }
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        loginError.classList.add('hidden-view');
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            // Nota el cambio en la ruta del fetch
            const response = await fetch('../app/api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();

            if (data.success) {
                checkAuthStatus(); 
            } else {
                loginError.innerText = data.message;
                loginError.classList.remove('hidden-view');
            }
        } catch (error) {
            loginError.innerText = "Error de conexión con el servidor.";
            loginError.classList.remove('hidden-view');
        }
    });

    if(btnLogout) {
        btnLogout.addEventListener('click', realizarLogout);
    }
    if(btnLogoutMobile) {
        btnLogoutMobile.addEventListener('click', realizarLogout);
    }

    // Y metes la lógica que ya tenías dentro de una funcioncita para no repetirla:
    async function realizarLogout() {
        try {
            const response = await fetch('../app/api/auth/logout.php', { method: 'POST' });
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
        viewLogin.classList.add('hidden-view');
        viewApp.classList.remove('hidden-view');

        const userDisplay = document.getElementById('user-name-display');
        if (userDisplay) userDisplay.innerText = user.nombre;
        
        // --- NUEVO: Incluir BASE_PATH en la URL visible ---
        const targetUrl = BASE_PATH + '/dash';
        if(window.location.pathname !== targetUrl) {
            window.history.pushState({}, '', targetUrl);
        }

        // Si la función existe (porque app.js cargó bien), arranca el dashboard
        if (typeof initApp === 'function') {
            initApp();
        }
    }

    function showLogin() {
        viewApp.classList.add('hidden-view');
        viewLogin.classList.remove('hidden-view');
        
        // --- NUEVO: Incluir BASE_PATH en la URL visible ---
        const targetUrl = BASE_PATH + '/login';
        if(window.location.pathname !== targetUrl) {
            window.history.pushState({}, '', targetUrl);
        }

        if (typeof initApp === 'function') {
            initApp();
        }
    }
    

    checkAuthStatus();
});