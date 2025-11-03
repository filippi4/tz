<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Organizations Catalog API",
 *     description="API для каталога организаций с возможностью поиска по геолокации, зданиям и видам деятельности",
 *
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="query",
 *     name="key",
 *     description="API ключ для доступа к API"
 * )
 *
 * @OA\Tag(
 *     name="Organizations",
 *     description="Операции с организациями"
 * )
 * @OA\Tag(
 *     name="Buildings",
 *     description="Операции со зданиями"
 * )
 * @OA\Tag(
 *     name="Activities",
 *     description="Операции с видами деятельности"
 * )
 */
abstract class Controller
{
    //
}
