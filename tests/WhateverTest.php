<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/stubs/GlobalStubs.php';
include_once __DIR__ . '/stubs/KernelStubs.php';
include_once __DIR__ . '/stubs/ModuleStubs.php';
include_once __DIR__ . '/stubs/MessageStubs.php';

class WhateverTest extends TestCase
{
    private $versionsPruefungID = '{9E4B7738-F674-AA63-BF53-0C44E2D80342}';

    public function setUp(): void
    {
        //Reset
        IPS\Kernel::reset();
        //Register our library we need for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');
        parent::setUp();
    }
    public function testSum()
    {
        $var1 = 3;
        $var2 = 4;

        $sum = $var1 + $var2;
        $this->assertEquals(7, $sum);
    }

    public function testParseXml()
    {
        $string = <<<XML
        <?xml version="1.0" encoding="utf-8" standalone="yes"?><CR><LF><GroupAddress-Export xmlns="http://knx.org/xml/ga-export/01"><CR><LF>  <GroupRange Name="Outdoor" RangeStart="1" RangeEnd="2047" /><CR><LF>  <GroupRange Name="Vorderhaus/Keller" RangeStart="2048" RangeEnd="4095" /><CR><LF>  <GroupRange Name="Vorderhaus/Bunker" RangeStart="4096" RangeEnd="6143" /><CR><LF>  <GroupRange Name="Vorderhaus/EG/Bad" RangeStart="6144" RangeEnd="8191" /><CR><LF>  <GroupRange Name="Vorderhaus/EG/SchlafZi" RangeStart="8192" RangeEnd="10239" /><CR><LF>  <GroupRange Name="Vorderhaus/EG/WohnZi" RangeStart="10240" RangeEnd="12287" /><CR><LF>  <GroupRange Name="Vorderhaus/EG/WC" RangeStart="12288" RangeEnd="14335" /><CR><LF>  <GroupRange Name="Vorderhaus/EG/EssZiKü" RangeStart="14336" RangeEnd="16383" /><CR><LF>  <GroupRange Name="Vorderhaus/Flur" RangeStart="16384" RangeEnd="18431" /><CR><LF>  <GroupRange Name="Vorderhaus/OG/EssWohnZiKü" RangeStart="18432" RangeEnd="20479" /><CR><LF>  <GroupRange Name="Vorderhaus/OG/Kinderzimmer" RangeStart="20480" RangeEnd="22527" /><CR><LF>  <GroupRange Name="Vorderhaus/OG/SchlafZi" RangeStart="22528" RangeEnd="24575" /><CR><LF>  <GroupRange Name="Vorderhaus/OG/WC" RangeStart="24576" RangeEnd="26623" /><CR><LF>  <GroupRange Name="Vorderhaus/OG/Bad" RangeStart="26624" RangeEnd="28671"><CR><LF>    <GroupRange Name="Aktoren" RangeStart="26624" RangeEnd="26879"><CR><LF>      <GroupAddress Name="Stripe" Address="13/0/0" Description="light dimmed switch" DPTs="DPST-1-1" /><CR><LF>      <GroupAddress Name="Stripe" Address="13/0/1" Description="light dimmed change" DPTs="DPST-3-7" /><CR><LF>      <GroupAddress Name="Stripe" Address="13/0/2" Description="light dimmed value" DPTs="DPST-5-1" /><CR><LF>      <GroupAddress Name="Stripe" Address="13/0/3" Description="light state switch" DPTs="DPST-1-1" /><CR><LF>      <GroupAddress Name="Stripe" Address="13/0/4" Description="light state value" DPTs="DPST-5-1" /><CR><LF>      <GroupAddress Name="Spiegel" Address="13/0/5" Description="light switched switch" DPTs="DPST-1-1" /><CR><LF>      <GroupAddress Name="Spiegel" Address="13/0/6" Description="light switched lock" DPTs="DPST-1-3" /><CR><LF>      <GroupAddress Name="Spiegel" Address="13/0/7" Description="light state switch" /><CR><LF>      <GroupAddress Name="Rollo" Address="13/0/8" Description="shading drive long" /><CR><LF>      <GroupAddress Name="Rollo" Address="13/0/9" Description="shading drive short" /><CR><LF>      <GroupAddress Name="Rollo" Address="13/0/10" Description="shading state position" /><CR><LF>      <GroupAddress Name="Rollo" Address="13/0/11" Description="shading drive position" /><CR><LF>    </GroupRange><CR><LF>    <GroupRange Name="Sensoren" RangeStart="26880" RangeEnd="27135" /><CR><LF>  </GroupRange><CR><LF>  <GroupRange Name="Vorderhaus/DG" RangeStart="28672" RangeEnd="30719" /><CR><LF></GroupAddress-Export>
        XML;

        $file = file_get_contents(__DIR__ . '/fixtures/gas_test.xml', true);
        // echo $file;
        $cleaned = preg_replace('/<CR><LF>*/', '', $file);
        // echo $cleaned;''
        $xml = simplexml_load_string($cleaned);
        $this->assertEquals('Stripe', (string) $xml->GroupRange[13]->GroupRange[0]->GroupAddress[0]['Name']);
    }
}