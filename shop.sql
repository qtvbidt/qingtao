CREATE DATABASE tp0330 CHARSET utf8

;USE tp0330

;# 供货商表
CREATE TABLE supplier (
  id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '名称',
  intro TEXT COMMENT '简介',
  sort TINYINT NOT NULL DEFAULT 20 COMMENT '排序 数字越小越靠前',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态-1删除   0隐藏   1正常'
) ENGINE = MYISAM COMMENT '供货商' ;
 
INSERT INTO supplier VALUES(NULL,'北京供货商','北京供货商的简介',20,1);
INSERT INTO supplier VALUES(NULL,'上海供货商','上海供货商的简介',20,1);
INSERT INTO supplier VALUES(NULL,'成都供货商','成都供货商的简介',20,1);
INSERT INTO supplier VALUES(NULL,'武汉供货商','武汉供货商的简介',20,1);
INSERT INTO supplier VALUES(NULL,'重庆供货商','重庆供货商的简介',20,1);