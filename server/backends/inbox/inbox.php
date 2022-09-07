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
             * @param $subscriberId
             * @return false|array
             */
            abstract function getMessages($subscriberId);

            /**
             * Пометить сообщения как доставленные
             * @param array $msgIds массив идентификаторов сообщений
             * @return bool
             */
            abstract function markMessagesAsDelivered($msgIds): bool;

            /**
             * Пометить сообщения как прочитанные
             * @param array $msgIds массив идентификаторов сообщений
             * @return bool
             */
            abstract function markMessagesAsRead($msgIds): bool;

            /**
             * Пометить все сообщения подписчика как доставленные
             * @param int $subscriberId идентификатор подписчика
             * @return bool
             */
            abstract function markAllMessagesAsDelivered($subscriberId): bool;

            /**
             * Пометить все сообщения подписчика как прочитанные
             * @param int $subscriberId идентификатор подписчика
             * @return bool
             */
            abstract function markAllMessagesAsRead($subscriberId): bool;

            /**
             * Отправить сообщение подписчику
             * @param int $subscriberId
             * @param string $msgText
             * @return bool
             */
            abstract function sendMessage($subscriberId, $msgText): bool;
        }
    }

