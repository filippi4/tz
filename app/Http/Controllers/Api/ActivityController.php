<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Получить список видов деятельности
     *
     * @OA\Get(
     *     path="/activities",
     *     summary="Получить список видов деятельности",
     *     description="Возвращает список видов деятельности с возможностью фильтрации по родителю, уровню и названию",
     *     tags={"Activities"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="ID родительского вида деятельности (0 для корневых элементов)",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Уровень вложенности (1-3)",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, maximum=3, example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Поиск по названию (частичное совпадение)",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="Торговля")
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
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="max_level", type="integer", example=3)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($value > 0 && ! Activity::where('id', $value)->exists()) {
                        $fail('The selected '.$attribute.' does not exist.');
                    }
                },
            ],
            'level' => 'nullable|integer|min:1|max:3',
            'name' => 'nullable|string|max:255',
        ]);

        $query = Activity::withCount(['organizations', 'children']);

        if ($request->has('parent_id')) {
            if ($request->parent_id === '0') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        $activities = $query->get();

        return response()->json([
            'success' => true,
            'data' => $activities,
            'meta' => [
                'total' => $activities->count(),
                'max_level' => 3,
            ],
        ]);
    }

    /**
     * Получить виды деятельности в виде дерева
     *
     * @OA\Get(
     *     path="/activities/tree",
     *     summary="Получить виды деятельности в виде дерева",
     *     description="Возвращает все виды деятельности в виде иерархической древовидной структуры",
     *     tags={"Activities"},
     *     security={{"ApiKeyAuth":{}}},
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
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="max_level", type="integer", example=3)
     *             )
     *         )
     *     )
     * )
     */
    public function tree(Request $request)
    {
        $query = Activity::withCount(['organizations', 'children']);
        $activities = $query->whereNull('parent_id')->get();
        $activities->load('children.children');

        return response()->json([
            'success' => true,
            'data' => $activities,
            'meta' => [
                'total' => $activities->count(),
                'max_level' => 3,
            ],
        ]);
    }

    /**
     * Получить вид деятельности по ID
     *
     * @OA\Get(
     *     path="/activities/{id}",
     *     summary="Получить вид деятельности по ID",
     *     description="Возвращает детальную информацию о виде деятельности, включая путь от корня дерева",
     *     tags={"Activities"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID вида деятельности",
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
     *         description="Вид деятельности не найден",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Activity not found"),
     *             @OA\Property(property="error_code", type="string", example="ACTIVITY_NOT_FOUND")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $activity = Activity::with(['parent', 'children'])
            ->withCount('organizations')
            ->find($id);

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found',
                'error_code' => 'ACTIVITY_NOT_FOUND',
            ], 404);
        }

        $path = [];
        $current = $activity;
        while ($current) {
            array_unshift($path, [
                'id' => $current->id,
                'name' => $current->name,
                'level' => $current->level,
            ]);
            $current = $current->parent;
        }

        $data = $activity->toArray();
        $data['path'] = $path;

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
