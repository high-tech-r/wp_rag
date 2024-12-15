CREATE TABLE post_vectors (
    post_id BIGINT(20) UNSIGNED NOT NULL,
    vector JSON NOT NULL,
    PRIMARY KEY (post_id),
    FOREIGN KEY (post_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
);