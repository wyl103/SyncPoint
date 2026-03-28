<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OilBless - App</title>
    <base href="/app_bless/public/">

    <link href="css/output.css" rel="stylesheet">

    <!-- <link href="fontawesome/css/all.min.css" rel="stylesheet"/> -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body { font-family: 'Space Grotesk', sans-serif; }
        
        /* Ajustes para el efecto 'filled' de los iconos */
        .material-symbols-outlined { 
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; 
        }
        .material-symbols-outlined.filled { 
            font-variation-settings: 'FILL' 1; 
        }
        
        .hidden-view { display: none !important; }
        #loading-screen { transition: opacity 0.3s ease; }
    </style>
</head>
<body class="bg-background-light text-charcoal antialiased h-screen overflow-hidden">
    <div id="loading-screen" class="absolute inset-0 bg-white z-[100] flex items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
    </div>