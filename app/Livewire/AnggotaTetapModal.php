<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AnggotaTetapModal extends Component
{
    public function render()
    {
        return view('livewire.anggota-tetap-modal', [
            'showModal' => session()->has('show_anggota_tetap_modal') && !Auth::user()->is_anggota_tetap
        ]);
    }
}
