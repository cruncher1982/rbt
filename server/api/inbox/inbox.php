<?php

namespace api\inbox
{
    use api\api;

    class inbox extends api
    {
        public static function POST($params) {
            $inbox = loadBackend("inbox");
            $success = $inbox->sendMessage($params["_id"], $params["msgText"]);
            return api::ANSWER($success);
        }

        public static function index()
        {
            return ["POST"];
        }
    }
}
