Slime Runner Milestone 4 Demo App
===================================

HOW TO USE
----------

1. Copy all PHP files in this folder into your web server document root
   inside a folder called `slime_runner`.

   Example (MAMP on Mac):
       /Applications/MAMP/htdocs/slime_runner/

2. Make sure your MySQL database `slime_runner_db` is already created
   and populated using the COMMANDS.sql from Milestone 3.

3. In phpMyAdmin (while logged in as root), run:

   CREATE USER IF NOT EXISTS 'slime_app'@'localhost' IDENTIFIED BY 'slime_app_password';
   GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE
   ON slime_runner_db.* TO 'slime_app'@'localhost';
   FLUSH PRIVILEGES;

4. Start Apache/MySQL (MAMP/XAMPP/etc).

5. In your browser, open:

   http://localhost/slime_runner/login.php

6. Register a new account or click "Continue as Guest". Then you can:
   - Start runs and finish them (Start Run -> fill form -> Finish Run)
   - View leaderboard, history, achievements, and skins
   - Export your history as CSV

If something breaks, check:
- That config.php has the correct DB name and password.
- That your `slime_runner_db` schema matches Milestone 3.
