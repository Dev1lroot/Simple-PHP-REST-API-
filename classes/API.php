<?php

class API
{
    function __construct()
    {
        $this->paths = [];
    }

    // Defines an Endpoint
    function set($path,$method,$callback)
    {
        if(!isset($this->paths[$path])) $this->paths[$path] = [];
        $this->paths[$path][strtoupper($method)] = $callback;
    }

    // Provides access to request data
    function request()
    {
        return (object) [
            "method" => strtoupper($_SERVER["REQUEST_METHOD"]),
            "path"   => $_SERVER["REQUEST_URI"],
        ];
    }

    // Provides access to request headers
    function getHeader($headerName)
    {
        $headerName = str_replace("-", "_", $headerName);
        $headerName = strtoupper($headerName);

        if(isset($_SERVER["HTTP_".$headerName]))
        {
            return $_SERVER["HTTP_".$headerName];
        }
        return null;
    }

    // TO-DO. Documentation generator
    function generateDocs()
    {
      
    }


    // Provides simple response
    function resp($code = 200, $message = [], $contentType = 'auto')
    {
        // Content Type
        if($contentType == 'auto')
        {
            if(is_array($message) || is_object($message)) header("Content-Type: application/json; charset=UTF-8");
            if(is_string($message)) header("Content-Type: text/plain; charset=UTF-8");
        }
        else
        {
            header("Content-Type: {$contentType}");
        }

        // Message
        if(is_array($message) || is_object($message))
        {
            $message = json_encode($message,JSON_UNESCAPED_UNICODE);
        }

        http_response_code($code);
        die($message);
    }

    // API main process to access endpoints by request
    function route()
    {
        $r = $this->request();

        // If path exists
        if(isset($this->paths[$r->path]))
        {
            // If method on path exists
            if(isset($this->paths[$r->path][$r->method]))
            {
                // If path + method has callable function
                if(is_callable($this->paths[$r->path][$r->method]))
                {
                    // Execute callback with API itself as parameter
                    call_user_func($this->paths[$r->path][$r->method], $this);
                }
                else
                {
                    $this->resp(500,[
                        "text" => "Bad Endpoint Callback",
                        "code" => "error.callback_error.{$r->method}",
                        "path" => $r->path
                    ]);
                }
            }
            else
            {
                $this->resp(405,[
                    "text" => "Method not allowed",
                    "code" => "error.method_not_allowed.{$r->method}",
                    "path" => $r->path
                ]);
            }
        }
        else
        {
            $this->resp(404,[
                "text" => "Endpoint not found",
                "code" => "error.endpoint_not_found.{$r->method}",
                "path" => $r->path
            ]);
        }
    }
}
?>
