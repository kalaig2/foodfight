# foodfight


1. Clone the files from git repository - "https://github.com/kalaig2/foodfight.git" in the project folder.
Ex: "var/www/foodfight"

2. Create a database for foodfight and import the data from  - "/foodfight/sql/foodfight_multi_new_07_10_2016.sql"

3. Change the following details in wp-config file

      1. define('DB_NAME', 'dbname');
      2. define('DB_USER', 'username');
      3. define('DB_PASSWORD', 'password');
      4. define('DOMAIN_CURRENT_SITE', 'testing-domain-url');
      5.    define('PATH_CURRENT_SITE', '/foodfight/');


3. Go to Url - "http://testing-domain-url/foodfight/dbreplace.php"
      1. Enter the database details or submit the pre-populated DB values.

      2. Enter the database details and click "Submit DB Details"

      3. Click "Continue"

      4. Enter the following details,

       Search for : //202.129.197.46:3245/foodfight_latest (Dont include http:)
       Replace with : //testing-domain-url/foodfight (Dont include http:)
       Click "Submit search string"

     5. Once completed, click back and enter the folder paths,

        Search for: /var/www/foodfight_latest
        Replace with : /project-folder-path

     6. Once completed, click back and enter the folder paths,

        Search for: /foodfight_latest/
        Replace with : /project-folder-name/ (ex: /foodfight/)

     7. Create a .htaccess file in the project folder and include the following

     RewriteEngine On
     RewriteBase /foodfight/
     RewriteRule ^index\.php$ - [L]

     # add a trailing slash to /wp-admin
     RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

     RewriteCond %{REQUEST_FILENAME} -f [OR]
     RewriteCond %{REQUEST_FILENAME} -d
     RewriteRule ^ - [L]
     RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
     RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
     RewriteRule . index.php [L]


     8. Run the url http://testing-domain-url/foodfight
        Admin - url :  http://testing-domain-url/foodfight/wp-admin
        User Name : ffsuperadmin
        Password : ffsuperadmin@123



     9. Go to  Settings -> Permalinks , and Click Save.

     10. Goto http://testing-domain-url/foodfight and check all the pages.











