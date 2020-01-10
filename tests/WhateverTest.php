<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/stubs/GlobalStubs.php';
include_once __DIR__ . '/stubs/KernelStubs.php';
include_once __DIR__ . '/stubs/ModuleStubs.php';
include_once __DIR__ . '/stubs/MessageStubs.php';

class WhateverTest extends TestCase
{
    private $versionsPruefungID = "{9E4B7738-F674-AA63-BF53-0C44E2D80342}";
    
    public function setUp(): void
    {
        //Reset
        IPS\Kernel::reset();
        //Register our library we need for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');
        parent::setUp();
    }
    public function testSum() {
        $var1 = 3;
        $var2 = 4;

        $sum = $var1 + $var2;
        $this->assertEquals(7, $sum);
    }
}