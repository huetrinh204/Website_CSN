
# Tests


The phpunit/phpunit package should be fetched/cloned, in the admin component's root folder (administrator/components/com_jfilters).
This file can be loaded through a `composer install` command.

## Locally
 1) Our IDE should be configured to use the phpunit.phar from our `vendor/phpunit` folder.      
 For PhpStorm this is: File | Settings | Languages & Frameworks | PHP | Test Frameworks > *Use Composer autoloader* : *BASE_PATH/administrator/components/com_jfilters/vendor/autoload.php*

2) In the test run configuration, in the setting *Use alternative configuration file*, we should set our own phpunit configuration, i.e. *BASE_PATH/administrator/components/com_jfilters/phpunit.xml*

3) Our Integration tests need connection to the database. For this to happen, we have to pass/export in the runtime environment the following vars:
    `JTEST_DB_HOST`, `JTEST_DB_USER`, `JTEST_DB_PASSWORD`, `JTEST_DB_TABLE_PREFIX` , `JTEST_DB_NAME`
 --
 In PhpStorm we can pass environment/runtime variables under `Run > Edit Configurations > Environment Variables` setting.
 E.g. `Environment Variables`:`JTEST_DB_HOST=JoomlaTests;JTEST_DB_PASSWORD=root;JTEST_DB_TABLE_PREFIX=prfx_;JTEST_DB_USER=root;JTEST_DB_NAME=jfTest`

If those environment variables are not passed, it will use those defined in the file: `phpunit.xml`

## In a docker container's shell
We have to execute the tests in our `apache-php` container.

1) Open the container's bash in interactive (`-it`) mode.
`docker exec -it <container_name_or_id> bash`
2) cd to our project's root folder. e.g. `cd /var/www/html/Joomla5-dev`
3) Make sure that our mysql user has sufficient privileges.
Open a new terminal and connect to the mysql container.
   * `docker exec -it <container_name_or_id> bash`
   * Connect to mysql as root. `mysql -u root -p`
   * Show the privileges of the db user (find the user in our `phpunit.xml`). `SHOW GRANTS FOR 'docker'@'%';`
   * Assign our user (e.g. `docker`) all the privileges for our db (e.g. `jfTest`)(find the db in our `phpunit.xml`). `GRANT ALL PRIVILEGES ON jfTest.* TO 'docker'@'%';FLUSH PRIVILEGES;`
   * Restart the mysql container. `docker compose restart mysql`
4) Run the test: `php /PATH_TO_PROJECT/administrator/components/com_jfilters/vendor/phpunit/phpunit/phpunit --configuration /PATH_TO_PROJECT/administrator/components/com_jfilters/phpunit.xml /PATH_TO_PROJECT/administrator/components/com_jfilters/src/Test`
 
## In the docker container from within PhpStorm
1. Go to `settings > PHP` find the "CLI Interpreter" setting and click on the double dots.   
3. Click + -> "From Docker"
  ![remote_interpreter](https://github.com/user-attachments/assets/c79ef68d-d25f-4aec-a289-75f3ec9396a8)

4. Select `Docker` as server. In `Image name` select the name of the docker instance, e.g. "docker-php-apache", and leave the `PHP Interpreter path` as it. Click "OK".
   ![php_interpreter](https://github.com/user-attachments/assets/deb87960-e4fe-42a2-b990-9a609038f9c1)

5. Go to `settings > PHP > Test Frameworks` and click the "+" to add new.
6. Select "PHPUnit by Remote interpreter"
7. Select the interpreter we have previously created.
    ![test_frameworks](https://github.com/user-attachments/assets/544ba5fa-e0f5-478c-a0c9-645b5bed0da9)
* In the `Use Composer Autoloader` field, the path should lead to the container's `autoload.php` NOT one on the host machine.
8. Set the network. In a terminal window find the name of our dockers' network by using `docker network ls`.
9. Once you know the network name e.g. "lamp", press the folder icon beside the "Docker Container" setting and in the setting named "Network Mode", add our network name.
10. Now go to `Run > Edit Configurations` press the "+" icon and add a new "PHPUnit" configuration.
11. Set "Directory" and select our docker interpreter as "Interpreter".
    ![run_configuration](https://github.com/user-attachments/assets/ca57a166-b763-432a-b8e2-2b9a4221eb36)


* Source: [Setting up remote php interpreter in PhpStorm](https://www.linkedin.com/pulse/phpstorm-docker-phpunit-xdebug-hernan-arregoces/)
* Source: [How to set the docker network in PhpStorm](https://github.com/lando/docs/issues/7)  

## Tips
Enable the debug by adding the *--debug* parameter to the cli command (through the Test Runner Options, in PhpStorm > Run)
