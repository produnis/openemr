CREATE TABLE IF NOT EXISTS form_track_anything (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  pid varchar(255) DEFAULT NULL,
  procedure_type_id bigint(20) DEFAULT NULL,
  comment varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS form_track_anything_results (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  track_anything_id bigint(20) DEFAULT NULL,
  track_timestamp datetime DEFAULT NULL,
  itemid bigint(20) DEFAULT NULL,
  result varchar(255) DEFAULT NULL,
  comment varchar(255) DEFAULT NULL,
  notes varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
