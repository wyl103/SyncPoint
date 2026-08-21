<div id="view-login" class="flex flex-col items-center justify-center h-full w-full p-6 bg-background-light absolute z-50">
    <div class="w-full max-w-sm space-y-6 bg-white p-8 rounded-2xl shadow-lg border border-gray-200">
        <div class="text-center">
            <span class="material-symbols-outlined text-primary text-5xl mb-2 filled">water_drop</span>
            <h1 class="text-2xl font-bold text-charcoal">OilBless</h1>
        </div>
        <div id="login-error" class="hidden-view bg-red-50 text-red-600 border border-red-200 p-3 rounded-lg text-sm text-center font-semibold"></div>
        <div id="login-success" class="hidden-view bg-green-50 text-green-700 border border-green-200 p-3 rounded-lg text-sm text-center font-semibold"></div>
        <form id="login-form" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700">Correo</label>
                <input type="email" id="email" class="mt-1 w-full rounded-lg border-gray-300 p-3 text-sm focus:border-primary focus:ring-primary" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700">Contraseña</label>
                <input type="password" id="password" class="mt-1 w-full rounded-lg border-gray-300 p-3 text-sm focus:border-primary focus:ring-primary" required>
            </div>
            <button type="submit" class="w-full bg-primary font-bold rounded-lg p-3 hover:bg-yellow-400">Ingresar</button>
        </form>
    </div>
</div>

<!-- VISTA DE REGISTRO DEL PRIMER USUARIO (SE MUESTRA CUANDO NO EXISTEN USUARIOS REGISTRADOS) -->
<div id="view-register-first" class="hidden-view flex flex-col items-center justify-center h-full w-full p-6 bg-background-light absolute z-50">
    <div class="w-full max-w-sm space-y-6 bg-white p-8 rounded-2xl shadow-lg border border-gray-200">
        <div class="text-center">
            <span class="material-symbols-outlined text-primary text-5xl mb-2 filled">person_add</span>
            <h1 class="text-2xl font-bold text-charcoal">Configuración Inicial</h1>
            <p class="text-xs text-gray-500 font-semibold mt-1">Crea la cuenta de usuario administrador para comenzar a usar la plataforma.</p>
        </div>
        <div id="register-first-error" class="hidden-view bg-red-50 text-red-600 border border-red-200 p-3 rounded-lg text-sm text-center font-semibold"></div>
        <form id="register-first-form" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700">Nombre completo</label>
                <input type="text" id="reg-nombre" placeholder="Ej: Wilman Arias" class="mt-1 w-full rounded-lg border-gray-300 p-3 text-sm focus:border-primary focus:ring-primary" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700">Correo electrónico</label>
                <input type="email" id="reg-email" placeholder="usuario@ejemplo.com" class="mt-1 w-full rounded-lg border-gray-300 p-3 text-sm focus:border-primary focus:ring-primary" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700">Contraseña</label>
                <input type="password" id="reg-password" placeholder="••••••••" class="mt-1 w-full rounded-lg border-gray-300 p-3 text-sm focus:border-primary focus:ring-primary" required>
            </div>
            <button type="submit" id="btn-register-first" class="w-full bg-primary font-bold rounded-lg p-3 hover:bg-yellow-400 transition">Crear Cuenta Administrador</button>
        </form>
    </div>
</div>