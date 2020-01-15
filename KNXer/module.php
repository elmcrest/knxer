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
        $rawData = base64_decode($this->ReadPropertyString('EtsXmlFile'));

        if (@simplexml_load_string($rawData)) {
            $data = simplexml_load_string($rawData);
            print_r($data, true);
            $name = $data->GroupRange[13]->GroupRange[0]->GroupAddress[0]['Name'];
            $this->SendDebug('KNXer', (string) $name, 0);
            $this->SetStatus(101);
        } else {
            $this->SetStatus(201);
            $this->ShowLastError("XML File couldn't be parsed. Is it a ETS5 XML Export file?");
        }

        parent::ApplyChanges();
    }

    // KX_ValidateEtsXmlGroupAddressExport($id)
    // public function ValidateEtsXmlGroupAddressExport()
    // {
    // }

    protected function ShowLastError(string $ErrorMessage, string $ErrorTitle = 'Error converting group addresses:')
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
