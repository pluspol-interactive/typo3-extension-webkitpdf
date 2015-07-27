#
# Table structure for table 'tx_webkitpdf_cache'
#
CREATE TABLE tx_webkitpdf_cache (
  uid INT(11) NOT NULL AUTO_INCREMENT,
  crdate INT(11) DEFAULT '0' NOT NULL,
  urls TEXT NOT NULL,
  filename TINYTEXT NOT NULL,
  PRIMARY KEY (uid)
);
