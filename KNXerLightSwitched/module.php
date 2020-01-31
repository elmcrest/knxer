<?php

declare(strict_types=1);

class KNXerLightSwitched extends IPSModule
{
    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('GALightSetSwitch', '0/0/0');
        $this->RegisterPropertyString('GASwitchSetLed', '0/0/0');

        $this->ConnectParent('{1C902193-B044-43B8-9433-419F09C641B8}');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    public function Send(string $Text)
    {
        $this->SendDataToParent(json_encode(['DataID' => '{42DFD4E4-5831-4A27-91B9-6FF1B2960260}', 'Buffer' => $Text]));
    }

    public function ReceiveData($JSONString)
    {
        //$this->SendDebug('raw', bin2hex($JSONString), 1);
        $data = json_decode($JSONString);
        $this->SendDebug("$data->GroupAddress1/$data->GroupAddress2/$data->GroupAddress3",
            print_r(utf8_decode($data->Data), true), 1);
    }
}

// 3/1/11

