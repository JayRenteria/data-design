DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS vote;

CREATE TABLE users (
	userId INT UNSIGNED AUTO_INCREMENT NOT NULL,
	email VARCHAR(128) NOT NULL,
	username VARCHAR(32) NOT NULL,
	PRIMARY KEY(userId)
);

CREATE TABLE comments (
	commentId INT UNSIGNED AUTO_INCREMENT NOT NULL,
	userId INT UNSIGNED NOT NULL,
	commentContent VARCHAR(15000) NOT NULL,
	commentDate DATETIME NOT NULL,
	INDEX(userId),
	FOREIGN KEY(userId) REFERENCES users(userId),
	PRIMARY KEY(commentId)
);

CREATE TABLE vote (
	userId INT UNSIGNED NOT NULL,
	commentId INT UNSIGNED NOT NULL,
	timeRecorded DATETIME NOT NULL,
	vote INT NOT NULL,
	INDEX(userId),
	INDEX(commentId),
	FOREIGN KEY(userId) REFERENCES users(userId),
	FOREIGN KEY(commentId) REFERENCES comments(commentId),
	PRIMARY KEY(userId, commentId),
	CHECK (vote = 1 or vote = -1)
);