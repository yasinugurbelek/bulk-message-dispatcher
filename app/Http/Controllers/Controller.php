<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Bulk Message API",
    version: "1.0.0",
    description: "Bulk Message API API Dokümantasyonu"
)]
#[OA\Server(url: 'http://localhost:8000', description: 'Local Server')]
abstract class Controller
{
    public function test() {}
}
