USE gametrade;

INSERT INTO games (name) VALUES
    ('루멘판타지'),
    ('철기전기'),
    ('아스트라온라인');

INSERT INTO game_servers (game_id, name)
SELECT g.id, pairs.server_name
FROM games AS g
INNER JOIN (
    SELECT '루멘판타지' AS game_name, '루멘' AS server_name
    UNION ALL SELECT '루멘판타지', '녹스'
    UNION ALL SELECT '철기전기', '1서버'
    UNION ALL SELECT '철기전기', '2서버'
    UNION ALL SELECT '아스트라온라인', '오리온'
    UNION ALL SELECT '아스트라온라인', '시리우스'
) AS pairs ON pairs.game_name = g.name;

-- 로컬 샘플 계정 seller1, seller2. 비밀번호는 README에만 적는다.
INSERT INTO users (username, password_hash) VALUES
    ('seller1', '$2y$12$Bfnq7QzR.QREbr8Z5agAcuEOXCCVp9phryEtrLFAIfleqWWFgW.Hm'),
    ('seller2', '$2y$12$Bfnq7QzR.QREbr8Z5agAcuEOXCCVp9phryEtrLFAIfleqWWFgW.Hm');

INSERT INTO items (user_id, server_id, item_name, price, quantity, description, status)
SELECT u.id, s.id, '빛나는 포션', 15000, 10, '샘플 판매 글입니다.', 'ON_SALE'
FROM users AS u
INNER JOIN game_servers AS s ON s.name = '루멘'
INNER JOIN games AS g ON g.id = s.game_id AND g.name = '루멘판타지'
WHERE u.username = 'seller1';

INSERT INTO items (user_id, server_id, item_name, price, quantity, description, status)
SELECT u.id, s.id, '강철 장검', 42000, 1, NULL, 'IN_PROGRESS'
FROM users AS u
INNER JOIN game_servers AS s ON s.name = '1서버'
INNER JOIN games AS g ON g.id = s.game_id AND g.name = '철기전기'
WHERE u.username = 'seller1';

INSERT INTO items (user_id, server_id, item_name, price, quantity, description, status)
SELECT u.id, s.id, '고대 코어', 900000, 2, NULL, 'COMPLETED'
FROM users AS u
INNER JOIN game_servers AS s ON s.name = '오리온'
INNER JOIN games AS g ON g.id = s.game_id AND g.name = '아스트라온라인'
WHERE u.username = 'seller1';

INSERT INTO items (user_id, server_id, item_name, price, quantity, description, status)
SELECT u.id, s.id, '마나 결정', 8000, 30, '판매자 seller2의 샘플입니다.', 'ON_SALE'
FROM users AS u
INNER JOIN game_servers AS s ON s.name = '녹스'
INNER JOIN games AS g ON g.id = s.game_id AND g.name = '루멘판타지'
WHERE u.username = 'seller2';
