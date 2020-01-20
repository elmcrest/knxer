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

        $this->ValidateEtsXmlGroupAddressExport();
        if ($this->GetStatus() < 200) {
            $this->GenerateBuildingFromImport();
        }
    }

    // KX_ValidateEtsXmlGroupAddressExport($id)
    public function ValidateEtsXmlGroupAddressExport()
    {
        $rawData = base64_decode($this->ReadPropertyString('EtsXmlFile'));

        if (@simplexml_load_string($rawData)) {
            $data = simplexml_load_string($rawData);
            $attr = $data->GroupRange[13]->GroupRange[0]->GroupAddress[0]->attributes();
            if (isset($attr['Description'])) {
                $name = $attr['Name'];
                $this->SendDebug('KNXer', (string) $name, 0);
                $this->SetStatus(101);
            } else {
                $this->SetStatus(202);
                $this->ShowError("XML File didn't meet the requirements. Is it a ETS5 XML Export file?");
            }
        } else {
            $this->SetStatus(201);
            $this->ShowError("XML File couldn't be parsed. Is it a ETS5 XML Export file?");
        }
    }

    public function GenerateBuildingFromImport()
    {
        $data = simplexml_load_string(base64_decode($this->ReadPropertyString('EtsXmlFile')));
        $building = [];
        foreach ($data as $section) {
            $exploded = explode('/', (string) $section->attributes()->Name);
            $part = [];
            $i = 0;
            while ($i < count($exploded)) {
                if ($i == 0) {
                    $part[$exploded[$i]] = [];
                } elseif ($i == 1) {
                    $part[$exploded[$i - 1]][$exploded[$i]] = [];
                } elseif ($i == 2) {
                    $part[$exploded[$i - 2]][$exploded[$i - 1]][$exploded[$i]] = [];
                } else {
                    throw new Exception();
                }
                $i++;
            }
            $building = array_merge_recursive($building, $part);
        }
        print_r($building);
    }
    protected function ShowError(string $ErrorMessage, string $ErrorTitle = 'Error converting group addresses:')
    {
        IPS_Sleep(500);
        $this->UpdateFormField('ErrorTitle', 'caption', $ErrorTitle);
        $this->UpdateFormField('ErrorText', 'caption', $ErrorMessage);
        $this->UpdateFormField('ErrorPopup', 'visible', true);
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
