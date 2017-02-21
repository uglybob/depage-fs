CONTAINER = depage-fs-local
INSTANCE = depage-fs-local0

.PHONY: build run-proftpd run-vsftpd kill enter clean

build:
	docker build -t $(CONTAINER) .

run-proftpd: build kill
	docker run --name $(INSTANCE) -v $(CURDIR)/../../../:/depage-fs --link depage-fs-proftpd0:depage-server -t -i $(CONTAINER) /bin/bash -c "/usr/local/bin/composer install; vendor/bin/phpunit --configuration Tests/PHPUnit.xml Tests"

run-vsftpd: build kill
	docker run --name $(INSTANCE) -v $(CURDIR)/../../../:/depage-fs --link depage-fs-vsftpd0:depage-server -t -i $(CONTAINER) /bin/bash -c "/usr/local/bin/composer install; vendor/bin/phpunit --configuration Tests/PHPUnit.xml Tests"

kill:
	-docker kill $(INSTANCE)
	-docker rm $(INSTANCE)

enter:
	docker exec -it $(INSTANCE) sh -c "export TERM=xterm && bash"

clean:
	-docker rm -f $(INSTANCE)
	-docker rmi -f $(CONTAINER):latest
