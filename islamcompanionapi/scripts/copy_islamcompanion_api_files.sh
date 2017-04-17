rsync -avz /var/www/html/pakjiddat/framework* /var/www/html/wordpresslocalhost/wp-content/plugins/islam-companion/;
rsync -avz /var/www/html/pakjiddat/islamcompanionapi/ /var/www/html/wordpresslocalhost/wp-content/plugins/islam-companion/islamcompanionapi/;
sed -i '/Database Information Start/,/Database Information End/d' /var/www/html/wordpresslocalhost/wp-content/plugins/islam-companion/islamcompanionapi/Configuration.php;
