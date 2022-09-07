create table inbox_subscribers_mobile
(
    msg_id              INTEGER not null
        primary key autoincrement,
    house_subscriber_id INTEGER not null,
    msg_date            TEXT    not null,
    msg_text            TEXT,
    flag_delivered      INTEGER(1) default 0,
    flag_read           INTEGER(1) default 0
);

create index inbox_house_subscriber_id_index
    on inbox_subscribers_mobile (house_subscriber_id);

