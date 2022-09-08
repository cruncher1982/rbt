<?php

//отправить тестовое сообщение
$inbox = loadBackend("inbox");
response(200, $inbox->sendMessage((int)@$postdata['subscriberId'], @$postdata['msg']));

