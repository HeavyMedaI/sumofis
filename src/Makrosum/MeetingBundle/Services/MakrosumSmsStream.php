<?php
/**
 * Created by PhpStorm.
 * User: musaatalay
 * Date: 25.03.2016
 * Time: 13:46
 */

namespace Makrosum\MeetingBundle\Services;


class MakrosumSmsStream
{
    private $Username, $Password, $Origin;

    public function __construct($username, $password, $origin){
        $this->Username = $username;
        $this->Password = $password;
        $this->Origin = $origin;
    }

    public function login()
    {
        $aToken = $this->execute("http://smsservice.artimesaj.com/api/login/", array(
            "Username" => $this->Username,
            "Password" => $this->Password,
            "Origin" => $this->Origin
        ));
        return rtrim(ltrim($aToken, "\""), "\"");
    }

    public function sendToGroup($Message, Array $Group, $Date = null, $isTurkish = false)
    {
        $StreamOptions = array(
            "MessageText" => $Message,
            "Orginator" => $this->Origin,
            "IsTurkish" => $isTurkish,
            "Date" => $Date,
            "Numbers" => $Group
        );
        $streamPath = "http://smsservice.artimesaj.com/api/singletextmessage?authToken={$this->login()}";
        return $this->execute($streamPath, $StreamOptions);
    }

    public function execute($prmPostAddress,$prmSendData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$prmPostAddress);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($prmSendData));
        $result = curl_exec($ch);
        return $result;
    }
}