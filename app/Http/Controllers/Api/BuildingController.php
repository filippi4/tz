<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /**
     * Получить список зданий
     *
     * @OA\Get(
     *     path="/buildings",
     *     summary="Получить список зданий",
     *     description="Возвращает список зданий с возможностью фильтрации по адресу",
     *     tags={"Buildings"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="Поиск по адресу (частичное совпадение)",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="Ленина")
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество элементов на странице",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=15)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=7)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неверный API ключ",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid API key"),
     *             @OA\Property(property="error_code", type="string", example="INVALID_API_KEY")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'address' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Building::withCount('organizations');

        if ($request->has('address')) {
            $query->where('address', 'like', '%'.$request->address.'%');
        }

        $perPage = $request->get('per_page', 15);
        $buildings = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $buildings->items(),
            'meta' => [
                'current_page' => $buildings->currentPage(),
                'per_page' => $buildings->perPage(),
                'total' => $buildings->total(),
                'last_page' => $buildings->lastPage(),
            ],
        ]);
    }

    /**
     * Получить здание по ID
     *
     * @OA\Get(
     *     path="/buildings/{id}",
     *     summary="Получить здание по ID",
     *     description="Возвращает детальную информацию о здании с количеством организаций",
     *     tags={"Buildings"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID здания",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Здание не найдено",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Building not found"),
     *             @OA\Property(property="error_code", type="string", example="BUILDING_NOT_FOUND")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $building = Building::withCount('organizations')->find($id);

        if (! $building) {
            return response()->json([
                'success' => false,
                'message' => 'Building not found',
                'error_code' => 'BUILDING_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $building,
        ]);
    }
}
