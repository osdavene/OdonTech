<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Facturación Electrónica DIAN y PTA (Sector Salud)
    |--------------------------------------------------------------------------
    */
    'pta_proveedor' => env('DIAN_PTA_PROVEEDOR', 'factus'), // factus u homologado
    'ambiente' => env('DIAN_AMBIENTE', 'sandbox'), // sandbox | produccion

    'factus' => [
        'url_sandbox' => env('DIAN_FACTUS_URL_SAND', 'https://api-sandbox.factus.com.co'),
        'url_produccion' => env('DIAN_FACTUS_URL_PROD', 'https://api.factus.com.co'),
        'client_id' => env('DIAN_FACTUS_CLIENT_ID', ''),
        'client_secret' => env('DIAN_FACTUS_CLIENT_SECRET', ''),
        'username' => env('DIAN_FACTUS_USERNAME', ''),
        'password' => env('DIAN_FACTUS_PASSWORD', ''),
        'api_token' => env('DIAN_FACTUS_TOKEN', ''),
    ],

    'prestador_salud' => [
        'nit' => env('ODONTECH_PRESTADOR_NIT', '901234567'),
        'dv' => env('ODONTECH_PRESTADOR_DV', '1'),
        'razon_social' => env('ODONTECH_PRESTADOR_RAZON_SOCIAL', 'CENTRO ODONTOLOGICO ODONTECH SAS'),
        'codigo_reps' => env('ODONTECH_PRESTADOR_CODIGO_REPS', '110010000001'), // Código de Habilitación MinSalud (12 dígitos)
        'municipio_codigo' => env('ODONTECH_PRESTADOR_MUNICIPIO', '11001'), // Bogotá DANE
        'direccion' => env('ODONTECH_PRESTADOR_DIRECCION', 'Calle 100 # 15-20 Consultorio 401'),
        'telefono' => env('ODONTECH_PRESTADOR_TELEFONO', '6013000000'),
        'correo' => env('ODONTECH_PRESTADOR_CORREO', 'facturacion@odontech.com.co'),
    ],
];
