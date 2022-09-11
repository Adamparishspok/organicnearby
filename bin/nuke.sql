--  sed -e s/EMAIL/daddooguy@gmail.com/g < nuke.sql | psql -U organicnearby 
with me AS (
	SELECT 'ftachico@gmail.com' AS login
	UNION
	SELECT 'daddooguy@gmail.com' AS login
	UNION
	SELECT 'knucklenails@gmail.com' AS login
	),
	d1 AS (delete from farmers using me, authlogins where  farmers.authlogins_id = authlogins.id AND authlogins.login = me.login),
	d2 AS (delete from users using me, authlogins where users.authlogins_id = authlogins.id AND authlogins.login = me.login)
	delete from authlogins using me where authlogins.login = me.login RETURNING *

