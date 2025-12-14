<?php

namespace App\Livewire\Pages\Account;

use Livewire\Component;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SettingsPage extends Component
{
    public function render()
    {
        return view('livewire.pages.account.settings-page');
    }





}
