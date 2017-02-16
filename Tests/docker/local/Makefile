.PHONY: build run kill enter clean test

build:
	docker build -t depage-fs-test .

test: run
	docker exec test-instance depage-fs/vendor/bin/phpunit --configuration PHPUnit.xml .

run: build kill
	docker run -d --name test-instance -v $(CURDIR)/../../../:/depage-fs --link server-vsftpd:vsftpd depage-fs-test

kill:
	-docker kill test-instance
	-docker rm test-instance

enter:
	docker exec -it test-instance sh -c "export TERM=xterm && bash"

clean:
	docker rm -f test-instance
	docker rmi -f depage-fs-test:latest
