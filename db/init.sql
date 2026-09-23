-- init.sql
-- Owner: sumaid
-- Table: verventech
-- Warning: contains traces of sarcasm. Side effects may include mild shame.

CREATE TABLE verventech (
serial_number   SERIAL PRIMARY KEY,
name            VARCHAR(50) NOT NULL,
labs_complete   INTEGER,
total_labs      INTEGER NOT NULL DEFAULT 61,
ranking         INTEGER
);

ALTER TABLE verventech OWNER TO sumaid;
-- Benevolent dictator of this table, a.k.a. the Table Hashira.
-- Demon Slayer fans, you know why. Everyone else, just nod respectfully.

INSERT INTO verventech (name, labs_complete, total_labs, ranking) VALUES
('sumaid',    55, 61, 1),
('danish',    36, 61, 2),
('amina',     42, 61, 3),
('moin',      41, 61, 4),
('musharaf',  NULL, 61, NULL);

-- A little scoreboard with commentary, because raw numbers are boring.
CREATE VIEW verventech_leaderboard AS
SELECT
ranking,
name,
labs_complete,
total_labs,
total_labs - labs_complete AS labs_remaining,
CASE
WHEN ranking = 1 THEN '🏆 lab final boss'
WHEN ranking = 2 THEN '⚔️ grinding toward the throne'
WHEN ranking = 3 THEN '🧪 experimental champion'
WHEN ranking = 4 THEN '🐢 loading the next achievement'
ELSE '🕵️ status: classified'
END AS commentary
FROM verventech
ORDER BY ranking NULLS LAST;

-- SELECT * FROM verventech_leaderboard;
-- Uncomment the line above to view the leaderboard.
