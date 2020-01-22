<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/stubs/GlobalStubs.php';
include_once __DIR__ . '/stubs/KernelStubs.php';
include_once __DIR__ . '/stubs/ModuleStubs.php';
include_once __DIR__ . '/stubs/MessageStubs.php';

class KNXerTest extends TestCase
{
    private $KNXerID = '{9E4B7738-F674-AA63-BF53-0C44E2D80342}';

    public function setUp(): void
    {
        //Reset
        IPS\Kernel::reset();
        //Register our library we need for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');
        parent::setUp();
    }

    public function testParseNonXml()
    {
        $KNXerModulID = IPS_CreateInstance($this->KNXerID);
        $filestring = base64_encode(file_get_contents(__DIR__ . '/fixtures/ga_test.txt', true));

        IPS_SetProperty($KNXerModulID, 'EtsXmlFile', $filestring);
        IPS_ApplyChanges($KNXerModulID);

        $KNXerInstance = IPS_GetInstance($KNXerModulID);
        $this->assertEquals(201, $KNXerInstance['InstanceStatus']);
    }
    public function testParseWrongXml()
    {
        $KNXerModulID = IPS_CreateInstance($this->KNXerID);
        // base64_encode to simulte upload via webinterface
        $filestring = base64_encode(file_get_contents(__DIR__ . '/fixtures/ga_test_ets4.xml', true));

        IPS_SetProperty($KNXerModulID, 'EtsXmlFile', $filestring);
        IPS_ApplyChanges($KNXerModulID);

        $KNXerInstance = IPS_GetInstance($KNXerModulID);
        $this->assertEquals(202, $KNXerInstance['InstanceStatus']);
    }
    public function testParseXml()
    {
        echo "\n#################START#####################\n";
        $KNXerModulID = IPS_CreateInstance($this->KNXerID);
        $filestring = base64_encode(file_get_contents(__DIR__ . '/fixtures/ga_test.xml', true));

        IPS_SetProperty($KNXerModulID, 'EtsXmlFile', $filestring);
        IPS_ApplyChanges($KNXerModulID);

        $KNXerInstance = IPS_GetInstance($KNXerModulID);
        $this->assertEquals(101, $KNXerInstance['InstanceStatus']);
        // $this->assertEquals(1, 1);
    }
}