drop table #__user;
CREATE TABLE #__user (
	userid INT AUTO_INCREMENT,
	username VARCHAR(50) NULL DEFAULT NULL,
	password VARCHAR(50) NULL DEFAULT NULL,
	createddate DATETIME NULL DEFAULT NULL,
	rightsadmin INT NULL DEFAULT 0,
	rightsbusiness INT NULL DEFAULT 0,
	rightsviewer INT NULL DEFAULT 0,
	realname VARCHAR(50) NULL DEFAULT NULL,
	loginlast DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (userid)
);

drop table #__userstamp;
CREATE TABLE #__userstamp (
	stampid INT AUTO_INCREMENT,
	userid INT NOT NULL,
	loginstamp VARCHAR(20) NULL DEFAULT NULL,
	PRIMARY KEY (stampid)
);

---default adminstrator: 12345678
INSERT INTO `#__user` (`userid`, `username`, `password`, `createddate`, `rightsadmin`, `rightsbusiness`, `rightsviewer`) 
VALUES(1, 'admin01', MD5('12345678'), '2015-09-21 00:00:00', 1, 1, 1);

drop table #__task;
CREATE TABLE #__task (
	taskid INT AUTO_INCREMENT,
	todo VARCHAR(512) NULL DEFAULT NULL,
	duedate INT NULL DEFAULT NULL,
	labelarr VARCHAR(50) NULL DEFAULT NULL,
	status INT NULL DEFAULT 0,
	userid INT NULL DEFAULT 0,
	createddate DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (taskid)
);

drop table #__label;
CREATE TABLE #__label (
	labelid INT AUTO_INCREMENT,
	labelname VARCHAR(30) NULL DEFAULT NULL,
	labelcolor VARCHAR(7) NULL DEFAULT NULL,
	createddate DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (labelid)
);
