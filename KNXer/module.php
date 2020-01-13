<?php

declare(strict_types=1);
// Klassendefinition
class KNXer extends IPSModule
{
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create()
    {
        // Diese Zeile nicht löschen.
        parent::Create();

        // $this->RegisterPropertyInteger("UpdateInterval", 12);

        // $this->RegisterVariableString("currentVersion", $this->Translate("Current Version"));
        // $this->RegisterVariableString("availableVersion", $this->Translate("Available Version"));

        // $this->RegisterTimer("CheckVersion", 0, "KX_UpdateVersion({$this->InstanceID});");
        $this->RegisterPropertyString('EtsXmlFile', '');
    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges()
    {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        // $this->SetTimerInterval("CheckVersion", $this->ReadPropertyInteger("UpdateInterval") * 60 * 60 * 1000);
        // $this->UpdateVersion();
    }

    public function IPS_GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'));
        $rawData = substr(base64_decode($this->ReadPropertyString('EtsXmlFile')), 0, 64);
        $xml = simplexml_load_string($rawData);
        IPS_LogMessage('KNXer', $xml);
        return json_encode($data);
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
