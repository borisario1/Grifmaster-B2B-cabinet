<?php

/**
 * Название: RecoveryPassController
 * Дата-время: 06-01-2026 16:47
 * Описание: здесь я управляю восстановлением аккаунта, 
 * когда это необходимо, отправляю код на через MailService.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RecoveryPassController extends Controller
{
    public function showRecoveryPass() { return view('auth.recovery_pass'); }

}