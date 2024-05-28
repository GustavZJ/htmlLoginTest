cp logintest.conf /etc/apache2/sites-available/logintest.conf

a2ensite logintest.conf
systemctl restart apache