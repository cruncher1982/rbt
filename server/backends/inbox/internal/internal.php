<?php

    /**
     * backends inbox namespace
     */

    namespace backends\inbox
    {
        class internal extends inbox
        {
            /**
             * @inheritDoc
             */
            function getMessages($subscriberId)
            {
                if (!checkInt($subscriberId)) {
                    return false;
                }

                return $this->db->get(
                    "select msg_id, msg_date, msg_text, flag_delivered, flag_read from inbox_subscribers_mobile where house_subscriber_id = :house_subscriber_id order by msg_date desc",
                    [
                        ":house_subscriber_id" => $subscriberId,
                    ],
                    [
                        "msg_id" => "msgId",
                        "msg_date" => "msgDate",
                        "msg_text" => "msgText",
                        "flag_delivered" => "flagDelivered",
                        "flag_read" => "flagRead"
                    ]
                );
            }

            /**
             * @inheritDoc
             */
            function countUnreadMessages($subscriberId): int
            {
                if (!checkInt($subscriberId)) {
                    return 0;
                }

                $r = $this->db->get(
                    "select count(*) cnt_unread from inbox_subscribers_mobile where house_subscriber_id = :house_subscriber_id and flag_read = 0",
                    [
                        ":house_subscriber_id" => $subscriberId,
                    ],
                    ["cnt_unread" => "countUnread"],
                    ["singlify"]
                );

                if (!$r) {
                    return 0;
                }

                return (int)$r['countUnread'];
            }

            /**
             * @inheritDoc
             */
            function markMessagesAsDelivered($msgIds)
            {
                foreach ($msgIds as $msgId) {
                    $this->db->modify("update inbox_subscribers_mobile set flag_delivered = 1 where msg_id = :msg_id",
                        [
                            ":msg_id" => $msgId
                        ]);
                }
            }

            /**
             * @inheritDoc
             */
            function markMessagesAsRead($msgIds)
            {
                foreach ($msgIds as $msgId) {
                    $this->db->modify("update inbox_subscribers_mobile set flag_read = 1 where msg_id = :msg_id",
                        [
                            ":msg_id" => $msgId
                        ]);
                }
            }

            /**
             * @inheritDoc
             */
            function markAllMessagesAsDelivered($subscriberId)
            {
                if (!checkInt($subscriberId)) {
                    return;
                }

                $this->db->modify("update inbox_subscribers_mobile set flag_delivered = 1 where house_subscriber_id = :subscriber_id",
                    [
                        ":subscriber_id" => $subscriberId
                    ]);
            }

            /**
             * @inheritDoc
             */
            function markAllMessagesAsRead($subscriberId)
            {
                if (!checkInt($subscriberId)) {
                    return;
                }

                $this->db->modify("update inbox_subscribers_mobile set flag_read = 1 where house_subscriber_id = :subscriber_id",
                    [
                        ":subscriber_id" => $subscriberId
                    ]);
            }

            /**
             * @inheritDoc
             */
            function sendMessage($subscriberId, $msgText): bool
            {
                if (!checkInt($subscriberId)) {
                    return false;
                }

                $households = loadBackend("households");
                $subscriber = $households->getSubscribers("id", $subscriberId)[0];

                if (!$subscriber) {
                    return false;
                }

                $msg_id = md5(time() + rand());
                $r = $this->db->insert("insert into inbox_subscribers_mobile(msg_id, house_subscriber_id, msg_date, msg_text) values(:msg_id, :subscriber_id, :msg_date, :msg_text)",
                    [
                        ":msg_id" => $msg_id,
                        ":subscriber_id" => $subscriberId,
                        ":msg_date" => date('Y-m-d H:i:s', time()),
                        ":msg_text" => $msgText,
                    ]);
                if (!$r) {
                    return false;
                }

                global $config;
                $isdn = loadBackend("isdn");
                $payload = [
                    "title" => @$config["backends"]["inbox"]["title"] ?: "Теледом",  // без этого поля пуш не уходит
                    "token" => $subscriber['pushToken'],
                    "messageId" => $msg_id,
                    "message_id" => $msg_id,
                    "msg" => $msgText,
                    "badge" => "1",
                ];

                return ($isdn->push($payload) == "OK");
            }
        }
    }

