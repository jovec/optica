<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    /**
     * Scope every query to the current organization and fill it in on create.
     */
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $query) {
            if (app()->bound('currentOrganization')) {
                $query->where(
                    $query->qualifyColumn('organization_id'),
                    static::currentOrganization()->id,
                );
            }
        });

        static::creating(function (Model $model) {
            if ($model->getAttribute('organization_id') === null && app()->bound('currentOrganization')) {
                $model->setAttribute('organization_id', static::currentOrganization()->id);
            }
        });
    }

    /**
     * Get the organization the record belongs to.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the organization identified for the current request.
     */
    protected static function currentOrganization(): Organization
    {
        /** @var Organization */
        return app('currentOrganization');
    }
}
