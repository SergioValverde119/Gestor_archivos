<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    Laravel\Wayfinder\WayfinderServiceProvider::class, // <-- AÑADE ESTA LÍNEA
];