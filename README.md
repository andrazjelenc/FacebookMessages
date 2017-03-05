# FacebookMessages
Order downloaded facebook messages


First download your own facebook data arhive (Settings->General->Download a copy of your Facebook data)

Create utf8_mb4 database on local MySQL server and import structure from _db. Then put file2db.php in the html subdir and using terminal run: "php file2db.php".

The script will collect your (not group) chats and save it in the db. For friends with more names you will be asked to choose which one you want to use. 

Then run:
//create table used by this script:
/*INSERT INTO daily_count (person_id, `date`, cnt)
SELECT m.person_id, date(m.datetime), COUNT(m.message_id) AS cnt
FROM messages m
WHERE m.type = 0
GROUP BY m.person_id, YEAR(m.datetime), MONTH(m.datetime), DAY(m.datetime)*/

To create new table that is used by scripts in www to draw different graphs
