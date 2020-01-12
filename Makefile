build:
	docker-compose up --build -d

setup:
	docker-compose run php composer install
	docker-compose run node yarn install --no-lockfile
	docker-compose run node yarn encore production
	docker-compose run php php bin/console d:d:c
	docker-compose run php php bin/console d:m:m --no-interaction
	docker-compose run php php bin/console d:d:c --env=test
	docker-compose run php php bin/console d:m:m --env=test --no-interaction

test:
	docker-compose run php php bin/phpunit

test_e2e:
	docker-compose run node yarn install --no-lockfile
	./node_modules/.bin/nightwatch

end:
	docker-compose down --volumes --rmi local
