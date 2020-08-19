# Apache Config

```apache
define ROOT "/home/loic/../../website_folder"
define SITE "mvc.local"

<VirtualHost *:80>
	ServerName ${SITE}
	ServerAlias *.${SITE}
	ServerAdmin admin@local

	DocumentRoot ${ROOT}
	DirectoryIndex index.php
	<Directory ${ROOT}>
		Require all granted

		# Allow local .htaccess to override Apache configuration settings
		AllowOverride all
	</Directory>

	<IfModule mod_headers.c>
	    # X-XSS-Protection
	    Header set X-XSS-Protection "1; mode=block"

	    # X-Frame-Options
	    Header always append X-Frame-Options SAMEORIGIN

	    # X-Content-Type nosniff
	    Header set X-Content-Type-Options nosniff
	</IfModule>

	CustomLog /var/log/apache2/mvc-access.log combined
	ErrorLog /var/log/apache2/mvc-error.log
	LogLevel warn
</VirtualHost>
```
