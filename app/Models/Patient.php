<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $first_name
 * @property string $last_name
 * @property Carbon|null $date_of_birth
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['first_name', 'last_name', 'date_of_birth', 'phone', 'email', 'notes'])]
class Patient extends Model
{
    use BelongsToOrganization;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }
}
