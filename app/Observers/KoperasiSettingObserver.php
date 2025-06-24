<?php

namespace App\Observers;

use App\Models\KoperasiSetting;
use Illuminate\Support\Facades\Cache;

class KoperasiSettingObserver
{
    public function saved(KoperasiSetting $setting)
    {
        Cache::forget("koperasi_setting.{$setting->key}");
    }

    public function deleted(KoperasiSetting $setting)
    {
        Cache::forget("koperasi_setting.{$setting->key}");
    }
}
