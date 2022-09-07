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
            function markMessagesAsDelivered($msgIds): bool
            {
                // TODO: Implement markMessagesAsDelivered() method.
            }

            /**
             * @inheritDoc
             */
            function markMessagesAsRead($msgIds): bool
            {
                // TODO: Implement markMessagesAsRead() method.
            }

            /**
             * @inheritDoc
             */
            function markAllMessagesAsDelivered($subscriberId): bool
            {
                // TODO: Implement markAllMessagesAsDelivered() method.
            }

            /**
             * @inheritDoc
             */
            function markAllMessagesAsRead($subscriberId): bool
            {
                // TODO: Implement markAllMessagesAsRead() method.
            }

            /**
             * @inheritDoc
             */
            function sendMessage($subscriberId, $msgText): bool
            {
                // TODO: Implement sendMessage() method.
            }
        }
    }

