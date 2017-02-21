CONTAINER = depage-fs-local
INSTANCE = depage-fs-local0

.PHONY: build run kill enter clean

build:
	docker build -t $(CONTAINER) .

run: build kill
	docker run --name $(INSTANCE) -v $(CURDIR)/../../../:/depage-fs --link depage-fs-vsftpd0:vsftpd -t -i $(CONTAINER) --configuration /depage-fs/Tests/PHPUnit.xml /depage-fs/Tests

kill:
	-docker kill $(INSTANCE)
	-docker rm $(INSTANCE)

enter:
	docker exec -it $(INSTANCE) sh -c "export TERM=xterm && bash"

clean:
	docker rm -f $(INSTANCE)
	docker rmi -f $(CONTAINER):latest
