.PHONY: build run kill enter clean

build:
	docker build -t depage-fs-test .

run: kill
	docker run --name test-instance -v $(CURDIR)/../../../:/depage-fs --link server-vsftpd:vsftpd -i -t depage-fs-test

kill:
	-docker kill test-instance
	-docker rm test-instance

enter:
	docker exec -it test-instance sh -c "export TERM=xterm && bash"

clean:
	docker rm -f test-instance
	docker rmi -f depage-fs-test:latest
