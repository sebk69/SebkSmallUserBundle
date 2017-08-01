CREATE TABLE `user` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(255) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `nickname`   VARCHAR(255) NOT NULL,
  `salt`       VARCHAR(255) NOT NULL,
  `enabled`    INT(1)       NOT NULL,
  `created_at` DATETIME     NOT NULL,
  `updated_at` DATETIME     NULL,
  PRIMARY KEY (`id`),
  UNIQUE `email` (`email`),
  UNIQUE `nickname` (`nickname`)
)
  ENGINE = InnoDB;