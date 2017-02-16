RM = rm -rf

PHONY: test vsftpd-server clean

test: vsftpd-server
	../vendor/bin/phpunit --configuration PHPUnit.xml .

vsftpd-server:
	$(MAKE) -C docker/remote-vsftpd
	$(MAKE) run -C docker/remote-vsftpd

clean:
	${RM} Report Temp docker/ssl/ca.pem
	$(MAKE) clean -C docker
