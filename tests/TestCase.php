<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
//use Illuminate\Foundation\Testing\WithoutMiddleware; // только для тестов с mirgations
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase; // Добавь это здесь, чтобы работало во всех тестах
}