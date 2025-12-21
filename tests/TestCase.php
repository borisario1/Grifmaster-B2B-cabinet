<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware; // Добавь это

abstract class TestCase extends BaseTestCase
{
    // Если хочешь отключить CSRF во ВСЕХ тестах сразу, 
    // расскомментируй строку ниже. Но лучше точечно в каждом файле.
    // use WithoutMiddleware; 
}