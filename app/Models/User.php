<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'alamat',
        'no_telepon',
        'is_anggota_tetap',
        'tanggal_menjadi_anggota_tetap',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_anggota_tetap' => 'boolean',
        'tanggal_menjadi_anggota_tetap' => 'date',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function simpanans()
    {
        return $this->hasMany(Simpanan::class);
    }

    public function pendaftaranAnggotaTetap()
    {
        return $this->hasOne(PendaftaranAnggotaTetap::class);
    }
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function pinjamans()
    {
        return $this->hasMany(Pinjaman::class);
    }

    public function produks()
    {
        return $this->hasMany(Produk::class);
    }

    public function pembelianSebagaiPembeli()
    {
        return $this->hasMany(PembelianProduk::class, 'pembeli_id');
    }

    public function pembelianSebagaiPenjual()
    {
        return $this->hasMany(PembelianProduk::class, 'penjual_id');
    }
    public function tagihan()
    {
        return $this->hasMany(TagihanAnggota::class);
    }
    public function shuDistributions()
    {
        return $this->hasMany(SHUDistribution::class);
    }

    public function saldoAnggota()
    {
        return $this->hasOne(SaldoAnggota::class);
    }

    public function shuPengambilans()
    {
        return $this->hasMany(SHUPengambilan::class);
    }
}
