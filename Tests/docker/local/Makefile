.PHONY: build run kill enter clean test

build:
	docker build -t depage-fs-test .

run: build kill
	docker run --name test-instance -v $(CURDIR)/../../../:/depage-fs --link server-vsftpd:vsftpd -t -i depage-fs-test --configuration /depage-fs/Tests/PHPUnit.xml /depage-fs/Tests

kill:
	-docker kill test-instance
	-docker rm test-instance

enter:
	docker exec -it test-instance sh -c "export TERM=xterm && bash"

clean:
	docker rm -f test-instance
	docker rmi -f depage-fs-test:latest
