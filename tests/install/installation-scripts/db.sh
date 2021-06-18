#!/bin/bash

RET=1
while [[ RET -ne 0 ]]; do
    echo "=> Waiting for confirmation of MariaDB service startup"
    sleep 5
    mysql -uroot -p${MYSQL_ROOT_PASSWORD} -e "status" > /dev/null 2>&1
    RET=$?
done

echo "Checking for databases to import from environment variables INSTALL_<DB_NAME>";
echo "=> Importing example database 'no_orm'"
mysql -uroot -p${MYSQL_ROOT_PASSWORD} -e "CREATE DATABASE IF NOT EXISTS no_orm"
mysql -uroot -p${MYSQL_ROOT_PASSWORD} -e "USE no_orm"
mysql -uroot -p${MYSQL_ROOT_PASSWORD} no_orm < install.sql

echo "=> Granting access to all databases for '${MYSQL_USER}'"
#mysql -uroot -p${MYSQL_ROOT_PASSWORD} -e "CREATE USER '${MYSQL_USER}'@'%' IDENTIFIED BY '{$MYSQL_PASSWORD}'"
mysql -uroot -p${MYSQL_ROOT_PASSWORD} -e "GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_USER}'@'%' WITH GRANT OPTION"

echo "=> Done!"