# BBDraft  
<center><img src="https://gitlab.com/laken/bbdraft/raw/master/images/logo.png" width="200px"></center>  
BBDraft is a PHP-based web app for playing Fantasy Big Brother (US & CA version). It is written in Object-Oriented PHP 7.1, and uses the Twig templating engine.  
BBDraft is still in its early development stage.  
### Setup
- To setup BBDraft on a server, you need the following: PHP 7.1, Twig, MySQL (with a DB named `bbdraft`, and Reddit API keys.  
- Once you have the following, run the following 2 SQL queries:  
```
CREATE TABLE `bbdraft`.`bbdraft_users` (
  `id` INT(11) NOT NULL,
  `identifier` VARCHAR(21) NULL,
  `provider` VARCHAR(6) NULL,
  `display_name` VARCHAR(60) NULL,
  `email` VARCHAR(60) NULL,
  `league` VARCHAR(255) NULL,
  `team_name` VARCHAR(30) NULL,
  `pick1` INT(2) NULL,
  `pick2` INT(2) NULL,
  `pick3` INT(2) NULL,
  `points` INT(3) NULL,
  PRIMARY KEY (`id`));
```
```
CREATE TABLE `bbdraft`.`bbdraft_leagues` (
  `code` VARCHAR(8) NOT NULL,
  `name` VARCHAR(30) NULL,
  `owner` VARCHAR(21) NULL,
  PRIMARY KEY (`code`));
``` 
- Then, enter your SQL user credentials and Reddit API keys into data.php, and put that outside of the webroot (Our webroot is in /var/www/html, so we put it in /var/www/), if your directories are setup differently, you may need to change a few lines (in connection.php and classes.php) of code.  