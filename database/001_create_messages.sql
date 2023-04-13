CREATE TABLE messages(
    id bigserial PRIMARY KEY,
    sender_id bigint REFERENCES users(id),
    text text
);