<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OilBless - App</title>
    <base href="/">

    <link href="css/output.css" rel="stylesheet">

    <!-- <link href="fontawesome/css/all.min.css" rel="stylesheet"/> -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        html, body {
            height: 100%;
            height: 100dvh;
            width: 100%;
            width: 100dvw;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        #view-app {
            height: 100%;
            height: 100dvh;
            width: 100%;
            width: 100dvw;
        }

        /* Ajustes para el efecto 'filled' de los iconos */
        .material-symbols-outlined { 
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; 
        }
        .material-symbols-outlined.filled { 
            font-variation-settings: 'FILL' 1; 
        }
        
        .hidden-view { display: none !important; }
        #loading-screen { transition: opacity 0.3s ease; }

        /* Espaciado inferior para que la barra de navegación fija en móvil no tape el contenido */
        @media (max-width: 767px) {
            .mobile-bottom-nav {
                padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0px)) !important;
            }
            .mobile-scroll-container {
                padding-bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px)) !important;
            }
            .chat-badge-ruta-desktop {
                display: none !important;
            }
            .chat-item-conv-id-desktop {
                display: none !important;
            }
        }

        /* --- ESTILOS DE LISTA DE CHATS --- */
        .chat-item-row {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.875rem !important;
            padding: 1rem 1.25rem !important;
            border-bottom: 1px solid #f3f4f6 !important;
            cursor: pointer !important;
            transition: background-color 0.15s ease !important;
            width: 100% !important;
            background-color: #ffffff !important;
            box-sizing: border-box !important;
        }
        .chat-item-row:hover {
            background-color: #fefce8 !important;
        }
        .chat-item-row:last-child {
            border-bottom: none !important;
        }
        .chat-item-avatar-wrapper {
            position: relative !important;
            flex-shrink: 0 !important;
            width: 44px !important;
            height: 44px !important;
        }
        .chat-item-avatar {
            width: 44px !important;
            min-width: 44px !important;
            height: 44px !important;
            min-height: 44px !important;
            border-radius: 50% !important;
            background-color: #fef08a !important;
            color: #2d3436 !important;
            font-weight: 800 !important;
            font-size: 1rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1.5px solid #fde047 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            user-select: none !important;
        }
        .chat-item-online-dot {
            position: absolute !important;
            bottom: 0 !important;
            right: 0 !important;
            width: 11px !important;
            height: 11px !important;
            background-color: #10b981 !important;
            border: 2px solid #ffffff !important;
            border-radius: 50% !important;
        }
        .chat-item-body {
            flex: 1 1 0% !important;
            min-width: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 0.2rem !important;
            overflow: hidden !important;
        }
        .chat-item-title-row {
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            min-width: 0 !important;
            overflow: hidden !important;
        }
        .chat-item-name {
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            color: #2d3436 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            display: block !important;
            min-width: 0 !important;
            flex: 1 1 auto !important;
        }
        .chat-item-conv-id {
            font-size: 0.6875rem !important;
            font-weight: 600 !important;
            color: #9ca3af !important;
            flex-shrink: 0 !important;
        }
        .chat-item-tags-row {
            display: flex !important;
            align-items: center !important;
            gap: 0.375rem !important;
            flex-wrap: wrap !important;
        }
        .chat-badge-sucursal {
            display: inline-flex !important;
            align-items: center !important;
            background-color: #f3f4f6 !important;
            color: #4b5563 !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            padding: 0.125rem 0.45rem !important;
            border-radius: 0.35rem !important;
            max-width: 160px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            border: 1px solid #e5e7eb !important;
        }
        .chat-badge-ruta {
            display: inline-flex !important;
            align-items: center !important;
            background-color: #fefce8 !important;
            color: #854d0e !important;
            border: 1px solid #fef08a !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            padding: 0.125rem 0.45rem !important;
            border-radius: 0.35rem !important;
            max-width: 160px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        .chat-item-message-preview {
            display: flex !important;
            align-items: center !important;
            gap: 0.375rem !important;
            min-width: 0 !important;
            width: 100% !important;
            overflow: hidden !important;
            padding-top: 0.1rem !important;
        }
        .chat-item-message-text {
            flex: 1 1 0% !important;
            min-width: 0 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            display: block !important;
            font-size: 0.75rem !important;
            color: #6b7280 !important;
            font-weight: 400 !important;
        }
        .chat-item-meta-right {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-end !important;
            justify-content: space-between !important;
            gap: 0.35rem !important;
            flex-shrink: 0 !important;
            margin-left: 0.5rem !important;
            min-height: 40px !important;
        }
        .chat-item-time {
            font-size: 0.6875rem !important;
            font-weight: 600 !important;
            color: #9ca3af !important;
            white-space: nowrap !important;
        }
        .chat-item-status-icon {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            min-height: 18px !important;
        }
    </style>
</head>
<body class="bg-background-light text-charcoal antialiased overflow-hidden">
    <div id="loading-screen" class="absolute inset-0 bg-white z-[100] flex items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
    </div>