<?php

/**
* 
*/
class root
{
    
    static public function messages()
    {
        $messages = array(
            "CET_score_url" => "https://api.xu42.cn/v1/cet/"
        );

        return json_encode($messages);
    }
}
