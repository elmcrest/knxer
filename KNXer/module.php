<?php

declare(strict_types=1);

class KNXer extends IPSModule
{
    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('EtsXmlFile', '');
        $this->RegisterAttributeString('KNXBuildingRepresentation', '');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->ValidateXml();
        if ($this->GetStatus() < 200) {
            $building_serialized = $this->GenerateBuilding();
            $this->WriteAttributeString('KNXBuildingRepresentation', $building_serialized);
            $this->CreateObjectTree(unserialize($building_serialized), 0, '');
        }
    }

    public function CreateObjectTree($root, $parentId, $key)
    {
        if ($key) {
            $parentId = $this->CreateCategoryByIdent(StringToSlug($key), $key, $parentId);
        }
        foreach ($root as $key => $sub) {
            $this->CreateCategoryByIdent(StringToSlug($key), $key, $parentId);
            if (isAssoc($sub)) {
                $this->CreateObjectTree($sub, $parentId, $key);
            } else {
                $this->CreateSmartObject($sub);
            }
        }
    }
    public function CreateSmartObject($arr)
    {
        if (!array_key_exists(0, $arr)) {
            return;
        }
        foreach ($arr[0] as $key => $smartObject) {
            echo "\n#####################################################\n";
            echo '$key: ' . print_r($key, true) . "\n";
            echo '$smartObject: ' . print_r($smartObject, true);
        }
    }
    // KX_ValidateEtsXmlGroupAddressExport($id)
    public function ValidateXml()
    {
        $xml = $this->GetXml();
        if ($xml) {
            $attr = $xml->xpath('//k:GroupAddress')[0];
            if (isset($attr['Description'])) {
                $this->SetStatus(102);
            } else {
                $this->SetStatus(202);
                $this->ShowError("XML File didn't meet the requirements. Is it a ETS5 XML Export file?");
            }
        } else {
            return;
        }
    }

    public function GenerateBuilding()
    {
        $xml = $this->GetXml();

        $building = [];
        foreach ($xml as $section) {
            $section->registerXPathNamespace('k', 'http://knx.org/xml/ga-export/01');
            $exploded = explode('/', (string) $section->attributes()->Name);
            $groupaddresses = [];
            foreach ($section->xpath("k:GroupRange[@Name='actuator' or @Name='sensor']") as $actuator) { // can only be a single GroupRange
                foreach ($actuator->GroupAddress as $groupaddress) {
                    $key = (string) $groupaddress->attributes()->Name;
                    if (isset($groupaddresses[$key])) {
                        // array already exists.
                    } else {
                        $groupaddresses[$key] = [];
                    }
                    $cleaned = [
                        'Type'        => (string) $groupaddress->xpath('..')[0]->attributes()->Name,
                        'Address'     => (string) $groupaddress->attributes()->Address,
                        'Description' => (string) $groupaddress->attributes()->Description,
                        'DPTs'        => (string) $groupaddress->attributes()->DPTs,
                    ];
                    array_push($groupaddresses[$key], $cleaned);
                }
            }
            $part = ArrayToNestedArray($exploded, []);
            if (count($groupaddresses) != 0) {
                switch (count($exploded)) {
                    case 0:
                    break;
                    case 1:
                        if (isset($part[$exploded[0]])) {
                            array_push($part[$exploded[0]], $groupaddresses);
                        } else {
                            $part[$exploded[0]] = [];
                            array_push($part[$exploded[0]], $groupaddresses);
                        }
                    break;
                    case 2:
                        if (isset($part[$exploded[0]][$exploded[1]])) {
                            array_push($part[$exploded[0]][$exploded[1]], $groupaddresses);
                        } else {
                            $part[$exploded[0]][$exploded[1]] = [];
                            array_push($part[$exploded[0]][$exploded[1]], $groupaddresses);
                        }
                    break;
                    case 3:
                        if (isset($part[$exploded[0]][$exploded[1]][$exploded[2]])) {
                            array_push($part[$exploded[0]][$exploded[1]][$exploded[2]], $groupaddresses);
                        } else {
                            $part[$exploded[0]][$exploded[1]][$exploded[2]] = [];
                            array_push($part[$exploded[0]][$exploded[1]][$exploded[2]], $groupaddresses);
                        }
                    break;
                }
            }

            $building = array_merge_recursive($building, $part);
        }
        return serialize($building);
    }

    private function CreateCategoryByIdent($ident, $name, $id = 0)
    {
        $cid = @IPS_GetObjectIDByIdent($ident, $id);
        if ($cid === false) {
            $cid = IPS_CreateCategory();
            if ($id !== 0) {
                IPS_SetParent($cid, $id);
            }
            IPS_SetName($cid, $name);
            IPS_SetIdent($cid, $ident);
        }
        return $cid;
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

function StringToSlug($string)
{
    return preg_replace('/[^0-9a-zA-Z ]/m', '', $string);
}

function isAssoc(&$arr)
{
    if (is_array($arr)) {
        reset($arr); // reset pointer to first element of array

        if (gettype(key($arr)) == 'string') { //get the type(nature) of first element key
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}