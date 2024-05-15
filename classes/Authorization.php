<?php

class Authorization
{
    function __construct()
    {
        $this->errors = [];
        $this->method = "Unknown";
        $this->params = (object) [];
    }
    function decode($input = null)
    {
        $this->input = $input;
        $this->method = "Unknown";
        $this->params = (object) [];

        if(strlen($this->input) > 0 && strpos($this->input," "))
        {
            $params = explode(" ",$this->input);

            if(count($params) == 2)
            {
                $method = strtolower($params[0]);

                if($method == "basic")
                {
                    try
                    {
                        $data = base64_decode($params[1]);
                        if(strpos($data,":"))
                        {
                            $ex = explode(":",$data);
                            if(count($ex) == 2)
                            {
                                $this->method = "Basic";
                                $this->params = (object) [
                                    "username" => $ex[0],
                                    "password" => $ex[1]
                                ];
                            }
                        }
                    }
                    catch(Exception $e)
                    {
                        $this->errors[] = ["text"=>"Unable to decode authorization","code"=>"error.authorization.basic.b64decode"];
                    }
                }
                else if($method == "bearer")
                {
                    $this->method = "Bearer";
                    $this->params = (object) [ "token" => $params[1] ];
                }
                else if($method == "digest")
                {
                    $this->method = "Digest";
                    // TO-DO.
                }
                else if($method == "fingerprint")
                {
                    try{
                        $data = base64_decode($params[1]);
                        try{
                            $data = (object) json_decode($data,true);
                            $this->method = "Fingerprint";
                            $this->params = (object) $data;
                        }
                        catch(Exception $e)
                        {
                            $this->errors[] = ["text"=>"Unable to decode authorization","code"=>"error.authorization.fingerprint.jsondecode"];
                        }
                    }
                    catch(Exception $e)
                    {
                        $this->errors[] = ["text"=>"Unable to decode authorization","code"=>"error.authorization.fingerprint.b64decode"];
                    }
                }
                else
                {
                    $this->errors[] = ["text"=>"Unable determine authorization method","code"=>"error.authorization.method_unsupported.{$method}"];
                }
            }
        }

        if($this->method == "Unknown") $this->errors[] = ["text"=>"Unable determine authorization method","code"=>"error.authorization.method_unsupported"];

        return $this;
    }
    function encode($method,$data)
    {
        // To Do
    }
    function getData()
    {
        return (object) [
            "method" => $this->method,
            "params" => $this->params
        ];
    }
    function getErrors()
    {
        return $this->errors;
    }
}

?>
