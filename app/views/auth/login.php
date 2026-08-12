<div id="view-login" class="hidden-view flex flex-col items-center justify-center h-full w-full p-6 bg-background-light absolute z-50">
    <div class="w-full max-w-sm space-y-6 bg-white p-8 rounded-2xl shadow-lg border border-gray-200">
        <div class="text-center">
            <span class="material-symbols-outlined text-primary text-5xl mb-2 filled">water_drop</span>
            <h1 class="text-2xl font-bold text-charcoal">OilBless</h1>
        </div>
        <div id="login-error" class="hidden-view bg-red-50 text-red-600 border border-red-200 p-3 rounded-lg text-sm text-center font-semibold"></div>
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