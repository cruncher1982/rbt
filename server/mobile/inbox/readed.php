<?php

/**
 * @api {post} /inbox/readed отметить сообщение (все сообщения) как прочитанное
 * @apiVersion 1.0.0
 * @apiDescription **[метод готов]**
 *
 * @apiGroup Inbox
 *
 * @apiHeader {String} authorization токен авторизации
 *
 * @apiParam {String} [messageId] идентификатор сообщения
 */

auth();

$inbox = loadBackend("inbox");
$msg_id = @$postdata['messageId'];
if (!$msg_id) {
    $inbox->markAllMessagesAsRead((int)$subscriber['subscriberId']);
} else {
    $inbox->markMessagesAsRead([$msg_id]);
}

response();
