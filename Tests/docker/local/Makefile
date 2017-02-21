CONTAINER = depage-fs-local
INSTANCE = depage-fs-local0

.PHONY: build run kill enter clean

build:
	docker build -t $(CONTAINER) .

run: build kill
	docker run --name $(INSTANCE) -v $(CURDIR)/../../../:/depage-fs --link depage-fs-vsftpd0:vsftpd -t -i $(CONTAINER) /bin/bash -c "/usr/local/bin/composer install; vendor/bin/phpunit --configuration Tests/PHPUnit.xml Tests"

kill:
	-docker kill $(INSTANCE)
	-docker rm $(INSTANCE)

enter:
	docker exec -it $(INSTANCE) sh -c "export TERM=xterm && bash"

clean:
	docker rm -f $(INSTANCE)
	docker rmi -f $(CONTAINER):latest
