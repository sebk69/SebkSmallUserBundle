CREATE TABLE `user_role` (
  `id_user` INT          NOT NULL,
  `role`    VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_user`, `role`)
)
  ENGINE = InnoDB;

ALTER TABLE `user_role`
  ADD CONSTRAINT `user`
FOREIGN KEY (`id_user`)
REFERENCES `user` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;