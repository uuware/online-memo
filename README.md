# online memo
The memo management system is an online system that allows a user to create, receive and search memo online at any time from any location via a browser. The memos are shown in a calendar form, so they are very easy to confirm the days related to those memos. The system provides reports of memos in calendar format and list format. Within the scope of the project specifications, there are several alternatives to develop the web application. The dynamic webpage can be developed using several programming technologies and there are several Database for choosing and it is running on PHP with MYSQL Database.


Install:
1, download Xampp and run it in your OS
2, unzip the source to webroot/demo of Xampp
3, change memo\inc\config.php to your environment
	'DBTYPE' => 'mysql',
	'DBHOST' => 'localhost',
	'DBNAME' => 'db-memo',
	'DBUSER' => 'root',
	'DBPASS' => '****',
4, run SQL Client to create database:db-memo
   and copy SQL from db.sql to create tables
5, enjoy http://localhost/memo/

![Screenshot](https://uuware.github.io/online-demo/login.png)
![Screenshot](https://uuware.github.io/online-demo/calendar-m.png)
![Screenshot](https://uuware.github.io/online-demo/calendar-y.png)
![Screenshot](https://uuware.github.io/online-demo/edit.png)
![Screenshot](https://uuware.github.io/online-demo/list.png)
![Screenshot](https://uuware.github.io/online-demo/edit2.png)
