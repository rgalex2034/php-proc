test: vendor
	./vendor/phpunit/phpunit/phpunit tests
vendor:
	composer install
# vim: set tabstop=4 shiftwidth=4 noexpandtab:
