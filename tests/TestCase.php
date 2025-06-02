<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\CreatesApplication; // Add this line

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication; // Add this line
    //
}
