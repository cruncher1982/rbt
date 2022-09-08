create table inbox_subscribers_mobile
(
    msg_id              TEXT                  not null
        primary key,
    house_subscriber_id INTEGER               not null,
    msg_date            TEXT                  not null,
    msg_text            TEXT       default '' not null,
    flag_delivered      INTEGER(1) default 0  not null,
    flag_read           INTEGER(1) default 0  not null
);

create index inbox_house_subscriber_id_index
    on inbox_subscribers_mobile (house_subscriber_id);

