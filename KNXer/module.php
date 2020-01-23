<?php

declare(strict_types=1);

class KNXer extends IPSModule
{
    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('EtsXmlFile', '');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->ValidateXml();
        if ($this->GetStatus() < 200) {
            $this->GenerateBuildingFromImport();
        }
    }

    // KX_ValidateEtsXmlGroupAddressExport($id)
    public function ValidateXml()
    {
        $xml = $this->GetXml();
        if ($xml) {
            $attr = $xml->xpath('//k:GroupAddress')[0];
            if (isset($attr['Description'])) {
                $this->SetStatus(101);
            } else {
                $this->SetStatus(202);
                $this->ShowError("XML File didn't meet the requirements. Is it a ETS5 XML Export file?");
            }
        } else {
            return;
        }
    }

    public function GenerateBuildingFromImport()
    {
        $xml = $this->GetXml();

        // $attr = $xml->xpath("//k:GroupRange[@Name='actuators']") + [null];
        $building = [];
        foreach ($xml as $section) {
            $section->registerXPathNamespace('k', 'http://knx.org/xml/ga-export/01');
            $exploded = explode('/', (string) $section->attributes()->Name);
            $part = ArrayToNestedArray($exploded, []);
            if ($section->xpath("k:GroupRange[@Name='actuators']")) {
                echo '####actuators###############';
            }
            if ($section->xpath("k:GroupRange[@Name='sensors']")) {
                echo '####sensors###############';
            }
            // $building = array_merge_recursive($building, $part);
        }
        // print_r($building);
    }
    private function ShowError(string $ErrorMessage, string $ErrorTitle = 'Error converting group addresses:')
    {
        IPS_Sleep(500);
        $this->UpdateFormField('ErrorTitle', 'caption', $ErrorTitle);
        $this->UpdateFormField('ErrorText', 'caption', $ErrorMessage);
        $this->UpdateFormField('ErrorPopup', 'visible', true);
    }

    private function GetXml()
    {
        $rawData = base64_decode($this->ReadPropertyString('EtsXmlFile'));
        if (@simplexml_load_string($rawData)) {
            $xml = simplexml_load_string($rawData);
            $xml->registerXPathNamespace('k', 'http://knx.org/xml/ga-export/01');
            return $xml;
        } else {
            $this->SetStatus(201);
            $this->ShowError("XML File couldn't be parsed. Is it a ETS5 XML Export file?");
            return;
        }
    }
    /**
     * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
     *
     * KX_UpdateVersion($id);
     *
     */
    // public function UpdateVersion()
    // {
    //     $this->SetValue("currentVersion", IPS_GetKernelVersion());

    //     $rawData = file_get_contents("https://apt.symcon.de/dists/stable/win/binary-i386/Packages");
    //     $xml = simplexml_load_string($rawData);
    //     $version = $xml->channel->item->enclosure->attributes("sparkle", true)->shortVersionString;
    //     $this->SetValue("availableVersion", strval($version));
    // }
}

function ArrayToNestedArray($arr_source, $arr_drain)
{
    $arr_tmp = $arr_source;
    if (array_pop($arr_tmp) != []) {
        $arr_drain = [array_pop($arr_source)=>$arr_drain];
        return ArrayToNestedArray($arr_source, $arr_drain);
    }
    return $arr_drain;
}