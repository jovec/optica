<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\ExaminationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $organization_id
 * @property int $patient_id
 * @property int|null $user_id
 * @property Carbon $examined_at
 * @property string|null $od_sphere
 * @property string|null $od_cylinder
 * @property int|null $od_axis
 * @property string|null $od_addition
 * @property string|null $os_sphere
 * @property string|null $os_cylinder
 * @property int|null $os_axis
 * @property string|null $os_addition
 * @property string|null $pd
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'examined_at',
    'od_sphere', 'od_cylinder', 'od_axis', 'od_addition',
    'os_sphere', 'os_cylinder', 'os_axis', 'os_addition',
    'pd', 'notes',
])]
class Examination extends Model
{
    /** @use HasFactory<ExaminationFactory> */
    use BelongsToOrganization, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'examined_at' => 'date',
            'od_sphere' => 'decimal:2',
            'od_cylinder' => 'decimal:2',
            'od_addition' => 'decimal:2',
            'os_sphere' => 'decimal:2',
            'os_cylinder' => 'decimal:2',
            'os_addition' => 'decimal:2',
            'pd' => 'decimal:1',
        ];
    }

    /**
     * Get the patient who was examined.
     *
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who did the examination.
     *
     * @return BelongsTo<User, $this>
     */
    public function examiner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the prescription for one eye ("od" or "os"), e.g. "-1.25 / -0.50 x 90".
     */
    public function prescription(string $eye): string
    {
        $sphere = $this->getAttribute("{$eye}_sphere");
        $cylinder = $this->getAttribute("{$eye}_cylinder");

        if ($sphere === null && $cylinder === null) {
            return '—';
        }

        $prescription = static::formatDiopter($sphere ?? '0');

        if ($cylinder !== null) {
            $prescription .= ' / '.static::formatDiopter($cylinder).' x '.$this->getAttribute("{$eye}_axis");
        }

        return $prescription;
    }

    /**
     * Get one diopter field formatted for display, or a dash when empty.
     */
    public function diopter(string $field): string
    {
        $value = $this->getAttribute($field);

        return $value === null ? '—' : static::formatDiopter($value);
    }

    /**
     * Format a diopter value with its sign and two decimals, e.g. "+1.50" or "-0.25".
     */
    public static function formatDiopter(string $value): string
    {
        $number = (float) $value;

        return ($number > 0 ? '+' : '').number_format($number, 2);
    }
}
