<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Repositories\ActivityRepository;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        protected ActivityRepository $activityRepository
    ) {}

    /**
     * Получить список организаций с фильтрацией
     *
     * @OA\Get(
     *     path="/organizations",
     *     summary="Получить список организаций",
     *     description="Возвращает список организаций с возможностью фильтрации по зданию, виду деятельности и названию",
     *     tags={"Organizations"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="building_id",
     *         in="query",
     *         description="ID здания для фильтрации",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="ID вида деятельности (включая дочерние)",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Поиск по названию организации (частичное совпадение)",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="ООО")
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
     *                 @OA\Property(property="last_page", type="integer", example=7),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=15)
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
            'building_id' => 'nullable|integer|exists:buildings,id',
            'activity_id' => 'nullable|integer|exists:activities,id',
            'name' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Organization::with(['buildings', 'activities', 'phones']);

        if ($request->has('building_id')) {
            $query->whereHas('buildings', function ($q) use ($request) {
                $q->where('buildings.id', $request->building_id);
            });
        }

        if ($request->has('activity_id')) {
            $activityIds = $this->activityRepository->getActivityWithChildren($request->activity_id);
            $query->whereHas('activities', function ($q) use ($activityIds) {
                $q->whereIn('activities.id', $activityIds);
            });
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        $perPage = $request->get('per_page', 15);
        $organizations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $organizations->items(),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'per_page' => $organizations->perPage(),
                'total' => $organizations->total(),
                'last_page' => $organizations->lastPage(),
                'from' => $organizations->firstItem(),
                'to' => $organizations->lastItem(),
            ],
        ]);
    }

    /**
     * Получить организацию по ID
     *
     * @OA\Get(
     *     path="/organizations/{id}",
     *     summary="Получить организацию по ID",
     *     description="Возвращает детальную информацию об организации включая здания, виды деятельности и телефоны",
     *     tags={"Organizations"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID организации",
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
     *         description="Организация не найдена",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Organization not found"),
     *             @OA\Property(property="error_code", type="string", example="ORGANIZATION_NOT_FOUND")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $organization = Organization::with(['buildings', 'activities.parent.parent', 'phones'])->find($id);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
                'error_code' => 'ORGANIZATION_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $organization,
        ]);
    }

    /**
     * Поиск организаций в радиусе от точки
     *
     * @OA\Get(
     *     path="/organizations/geo/radius",
     *     summary="Поиск организаций в радиусе от точки",
     *     description="Возвращает организации в заданном радиусе от координат с возможностью фильтрации",
     *     tags={"Organizations"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="Широта центра поиска",
     *         required=true,
     *
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90, example=55.7558)
     *     ),
     *
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         description="Долгота центра поиска",
     *         required=true,
     *
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180, example=37.6173)
     *     ),
     *
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Радиус поиска в метрах",
     *         required=true,
     *
     *         @OA\Schema(type="integer", minimum=100, maximum=50000, example=1000)
     *     ),
     *
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="ID вида деятельности для фильтрации",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Поиск по названию организации",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="ООО")
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
     *                 @OA\Property(
     *                     property="search_params",
     *                     type="object",
     *                     @OA\Property(
     *                         property="center",
     *                         type="object",
     *                         @OA\Property(property="latitude", type="number", example=55.7558),
     *                         @OA\Property(property="longitude", type="number", example=37.6173)
     *                     ),
     *                     @OA\Property(property="radius_meters", type="integer", example=1000)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function geoRadius(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|min:-90|max:90',
            'longitude' => 'required|numeric|min:-180|max:180',
            'radius' => 'required|integer|min:100|max:50000',
            'activity_id' => 'nullable|integer|exists:activities,id',
            'name' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $lat = $request->latitude * 10000000;
        $lon = $request->longitude * 10000000;
        $radius = $request->radius;

        $query = Organization::query()
            ->select('organizations.*')
            ->join('building_organization', 'organizations.id', '=', 'building_organization.organization_id')
            ->join('buildings', 'building_organization.building_id', '=', 'buildings.id')
            ->whereRaw(
                '(6371000 * acos(LEAST(1, GREATEST(-1, cos(radians(? / 10000000)) * cos(radians(buildings.latitude / 10000000)) * cos(radians(buildings.longitude / 10000000) - radians(? / 10000000)) + sin(radians(? / 10000000)) * sin(radians(buildings.latitude / 10000000)))))) <= ?',
                [$lat, $lon, $lat, $radius]
            )
            ->distinct()
            ->with(['buildings', 'activities', 'phones']);

        if ($request->has('activity_id')) {
            $activityIds = $this->activityRepository->getActivityWithChildren($request->activity_id);
            $query->whereHas('activities', function ($q) use ($activityIds) {
                $q->whereIn('activities.id', $activityIds);
            });
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        $perPage = $request->get('per_page', 15);
        $organizations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $organizations->items(),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'per_page' => $organizations->perPage(),
                'total' => $organizations->total(),
                'search_params' => [
                    'center' => [
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ],
                    'radius_meters' => $radius,
                ],
            ],
        ]);
    }

    /**
     * Поиск организаций в прямоугольной области
     *
     * @OA\Get(
     *     path="/organizations/geo/bounds",
     *     summary="Поиск организаций в прямоугольной области",
     *     description="Возвращает организации в заданной прямоугольной области по координатам",
     *     tags={"Organizations"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="min_lat",
     *         in="query",
     *         description="Минимальная широта (юго-западный угол)",
     *         required=true,
     *
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90, example=55.7)
     *     ),
     *
     *     @OA\Parameter(
     *         name="max_lat",
     *         in="query",
     *         description="Максимальная широта (северо-восточный угол)",
     *         required=true,
     *
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90, example=55.8)
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_lon",
     *         in="query",
     *         description="Минимальная долгота (юго-западный угол)",
     *         required=true,
     *
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180, example=37.5)
     *     ),
     *
     *     @OA\Parameter(
     *         name="max_lon",
     *         in="query",
     *         description="Максимальная долгота (северо-восточный угол)",
     *         required=true,
     *
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180, example=37.7)
     *     ),
     *
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="ID вида деятельности для фильтрации",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Поиск по названию организации",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="ООО")
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
     *                 @OA\Property(
     *                     property="search_params",
     *                     type="object",
     *                     @OA\Property(
     *                         property="bounds",
     *                         type="object",
     *                         @OA\Property(
     *                             property="southwest",
     *                             type="object",
     *                             @OA\Property(property="latitude", type="number", example=55.7),
     *                             @OA\Property(property="longitude", type="number", example=37.5)
     *                         ),
     *                         @OA\Property(
     *                             property="northeast",
     *                             type="object",
     *                             @OA\Property(property="latitude", type="number", example=55.8),
     *                             @OA\Property(property="longitude", type="number", example=37.7)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function geoBounds(Request $request)
    {
        $request->validate([
            'min_lat' => 'required|numeric|min:-90|max:90',
            'max_lat' => 'required|numeric|min:-90|max:90',
            'min_lon' => 'required|numeric|min:-180|max:180',
            'max_lon' => 'required|numeric|min:-180|max:180',
            'activity_id' => 'nullable|integer|exists:activities,id',
            'name' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $minLat = $request->min_lat * 10000000;
        $maxLat = $request->max_lat * 10000000;
        $minLon = $request->min_lon * 10000000;
        $maxLon = $request->max_lon * 10000000;

        $query = Organization::with(['buildings', 'activities', 'phones'])
            ->whereHas('buildings', function ($q) use ($minLat, $maxLat, $minLon, $maxLon) {
                $q->whereBetween('latitude', [$minLat, $maxLat])
                    ->whereBetween('longitude', [$minLon, $maxLon]);
            });

        if ($request->has('activity_id')) {
            $activityIds = $this->activityRepository->getActivityWithChildren($request->activity_id);
            $query->whereHas('activities', function ($q) use ($activityIds) {
                $q->whereIn('activities.id', $activityIds);
            });
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        $perPage = $request->get('per_page', 15);
        $organizations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $organizations->items(),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'per_page' => $organizations->perPage(),
                'total' => $organizations->total(),
                'search_params' => [
                    'bounds' => [
                        'southwest' => [
                            'latitude' => $request->min_lat,
                            'longitude' => $request->min_lon,
                        ],
                        'northeast' => [
                            'latitude' => $request->max_lat,
                            'longitude' => $request->max_lon,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
