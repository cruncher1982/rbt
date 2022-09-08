<?php

    /**
     * backends inbox namespace
     */

    namespace backends\inbox
    {
        use backends\backend;

        /**
         * base inbox class
         */
        abstract class inbox extends backend
        {
            /**
             * Получить список всех сообщений подписчика
             * @param int $subscriberId идентификатор подписчика
             * @return false|array
             */
            abstract function getMessages($subscriberId);

            /**
             * Получить количество непрочитанных сообщений
             * @param int $subscriberId идентификатор подписчика
             * @return int
             */
            abstract function countUnreadMessages($subscriberId): int;

            /**
             * Пометить сообщения как доставленные
             * @param array $msgIds массив идентификаторов сообщений
             */
            abstract function markMessagesAsDelivered($msgIds);

            /**
             * Пометить сообщения как прочитанные
             * @param array $msgIds массив идентификаторов сообщений
             */
            abstract function markMessagesAsRead($msgIds);

            /**
             * Пометить все сообщения подписчика как доставленные
             * @param int $subscriberId идентификатор подписчика
             */
            abstract function markAllMessagesAsDelivered($subscriberId);

            /**
             * Пометить все сообщения подписчика как прочитанные
             * @param int $subscriberId идентификатор подписчика
             * @return bool
             */
            abstract function markAllMessagesAsRead($subscriberId);

            /**
             * Отправить сообщение подписчику
             * @param int $subscriberId
             * @param string $msgText
             * @return bool
             */
            abstract function sendMessage($subscriberId, $msgText): bool;
        }
    }

