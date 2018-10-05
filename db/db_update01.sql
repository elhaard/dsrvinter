ALTER TABLE person ADD COLUMN hours int(10) unsigned NULL DEFAULT NULL;
ALTER TABLE person ADD COLUMN km int(10) unsigned NULL DEFAULT NULL;

UPDATE person p
 JOIN roer_kategori k ON p.kategori = k.ID
set p.hours = k.timer;


ALTER TABLE person MODIFY COLUMN hours int(10) unsigned  NOT NULL;
