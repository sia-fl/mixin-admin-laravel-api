dev-api:
	php -S 0.0.0.0:8080 public/index.php

dev:
	php artisan serve --host=0.0.0.0 --port=8080
