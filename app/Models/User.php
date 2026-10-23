<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'campus_id',
        'unit_id',
        'employment_type',
        'unit_section',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public const ROLE_SYSTEM_ADMIN = 'system_admin';
    public const ROLE_CAMPUS_HEAD = 'campus_head';
    public const ROLE_UNIT_HEAD = 'unit_head';
    public const ROLE_UNIT_MEMBER = 'unit_member';

    public static function employmentTypeLabels(): array
    {
        return [
            'staff' => 'Staff',
            'intern' => 'Magang',
        ];
    }

    public static function unitSectionLabels(): array
    {
        return [
            'campus_head' => 'Kepala Kampus',
            'admin_so' => 'Admin & SO',
            'marcomm' => 'Marketing Communication',
            'bsi_career_center' => 'BSI Career Center (BCC)',
            'bsi_entrepreneur_center' => 'BSI Enterpreneur Center',
            'kemahasiswaan' => 'Kemahasiswaan',
            'technical_support' => 'Technical Support',
        ];
    }

    public static function unitSectionUnitNames(): array
    {
        return [
            'admin_so' => 'Admin & SO',
            'marcomm' => 'Marketing Communication',
            'bsi_career_center' => 'BSI Career Center',
            'bsi_entrepreneur_center' => 'BSI Entrepreneur Center',
            'kemahasiswaan' => 'Kemahasiswaan',
            'technical_support' => 'Technical Support',
        ];
    }

    public function employmentTypeLabel(): string
    {
        return self::employmentTypeLabels()[$this->employment_type] ?? 'Belum ditentukan';
    }

    public function unitSectionLabel(): string
    {
        if (isset(self::unitSectionLabels()[$this->unit_section])) {
            return self::unitSectionLabels()[$this->unit_section];
        }

        if ($this->role === self::ROLE_SYSTEM_ADMIN) {
            return $this->roleLabel();
        }

        if ($this->role === self::ROLE_CAMPUS_HEAD) {
            return self::unitSectionLabels()['campus_head'];
        }

        $unitName = $this->unit?->name;
        $section = array_search($unitName, self::unitSectionUnitNames(), true);

        return $section === false
            ? $this->roleLabel()
            : self::unitSectionLabels()[$section];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function managedCampus(): HasMany
    {
        return $this->hasMany(Campus::class, 'head_user_id');
    }

    public function isSystemAdmin(): bool
    {
        return $this->role === self::ROLE_SYSTEM_ADMIN;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function assignedCampusId(): ?int
    {
        if ($this->role === self::ROLE_CAMPUS_HEAD) {
            return $this->campus_id === null ? null : (int) $this->campus_id;
        }

        if (in_array($this->role, [self::ROLE_UNIT_HEAD, self::ROLE_UNIT_MEMBER], true)) {
            return $this->unit?->campus_id === null ? null : (int) $this->unit->campus_id;
        }

        return null;
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_SYSTEM_ADMIN => 'Admin Sistem',
            self::ROLE_CAMPUS_HEAD => 'Kepala Kampus',
            self::ROLE_UNIT_HEAD => 'Kepala Unit',
            self::ROLE_UNIT_MEMBER => 'Anggota Unit',
            default => 'Peran belum ditentukan',
        };
    }
}
