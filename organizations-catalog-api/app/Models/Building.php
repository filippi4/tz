<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $address
 * @property int $latitude
 * @property int $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organization> $organizations
 * @property-read int|null $organizations_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Building whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Building extends Model
{
    protected $fillable = ['address', 'latitude', 'longitude'];

    protected $hidden = ['pivot'];

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class);
    }
}
