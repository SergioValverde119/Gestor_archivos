<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Bienvenido" />

    <!-- Contenedor principal con fondo claro -->
    <div class="relative min-h-screen bg-white text-gray-900 flex flex-col items-center justify-center p-6 transition-colors duration-300">
        <div class="w-full max-w-lg mb-8">
            <!-- Arte SVG minimalista con líneas oscuras y figuras adicionales -->
            <svg class="w-full h-auto" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Nuevas figuras de vectores para el fondo -->
                <circle cx="50" cy="150" r="10" fill="#E5E7EB" />
                <path d="M70 200 L120 180 M280 280 L320 250" stroke="#D1D5DB" stroke-width="1" />
                <rect x="300" y="50" width="30" height="30" rx="4" stroke="#E5E7EB" stroke-width="2" />
                <path d="M350 180 L380 210 L350 240 Z" fill="#D1D5DB" />
                <circle cx="20" cy="280" r="5" fill="#9CA3AF" />
                <path d="M10 20 L40 50 M360 20 L390 50" stroke="#D1D5DB" stroke-width="2" />
                
                <!-- Figuras principales (edificios y documentos) -->
                <path d="M100 250H300" stroke="#E5E7EB" stroke-width="2" />
                <path d="M150 250V150H250V250" stroke="#1F2937" stroke-width="2" />
                <path d="M200 150V50" stroke="#1F2937" stroke-width="2" stroke-dasharray="4 4" />
                
                <!-- Icono de documento minimalista -->
                <rect x="180" y="100" width="40" height="50" rx="4" stroke="#4B5563" stroke-width="2" fill="none"/>
                <path d="M190 100V110H210" stroke="#4B5563" stroke-width="2" />
                <path d="M190 120H210" stroke="#4B5563" stroke-width="2" />
                <path d="M190 130H210" stroke="#4B5563" stroke-width="2" />

                <!-- Puntos de conexión abstractos -->
                <circle cx="150" cy="250" r="4" fill="#60A5FA" />
                <circle cx="250" cy="250" r="4" fill="#60A5FA" />
                <circle cx="200" cy="50" r="4" fill="#60A5FA" />
            </svg>
        </div>

        <div class="text-center max-w-2xl mx-auto">
            <!-- Título principal -->
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-gray-900 mb-4">
                Bienvenido al Portal de Documentos de SEDUVI
            </h1>
            
            <!-- Subtítulo y descripción -->
            <p class="text-base md:text-lg text-gray-600 max-w-lg mx-auto">
                Herraniebta para la gestión de documentos de la Secretaría de Desarrollo Urbano y Vivienda SPOTMET
            </p>
        </div>
    </div>
</template>